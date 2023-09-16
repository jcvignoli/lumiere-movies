/** 
 * Infomaniak root website workflow
 *
 * Changed files are directly uploaded to the main server by ssh
 * Rsync available to syncronize it all
 * Errors notified (notify)
 * Can use external parameters to modify tasks behaviour (--clean yes, --nodry yes, --ssh yes)
 * Copying taks must be run --ssh yes to upload to ssh external server
 */

import gulp from 'gulp';
import browserSync from 'browser-sync';
import plumber from 'gulp-plumber';
import notify from 'gulp-notify';
import longerif from 'gulp-if';
import ssh from 'gulp-ssh';
import autoprefixer from 'gulp-autoprefixer';
import cleanCss from 'gulp-clean-css';
import changed from 'gulp-changed';
import rename from 'gulp-rename';
import uglify from 'gulp-uglify';
import imagemin from 'gulp-imagemin';
import del from 'del';
import fs from 'fs';
import rsync from 'gulp-rsync';
import nodeNotifier from 'node-notifier';
import ext_cred from '../../../bin/.credentials/.gulpcredentials-lumiere.js';	/* private credentials for ssh */

var errorHandler = function(error) {				/* handle and display errors with notify */
	notify.onError({
		title: '[' + error.plugin + '] Task Failed',
		message: error.message,
		icon: ext_cred.base.gulpimg,
		sound: true
	})(error);
	console.log("Error:", error.toString()); 
	/* this.emit('end'); removed, now called in plumber */
};

// Constant to get from the command-line "--rsync nodry" for rsync task or "--build clean" for build task or "--ssh yes" for running building tasks with ssh upload
var arg = (argList => {

	let arg = {}, nodry, clean, ssh, opt, thisOpt, curOpt;

	for (nodry = 0; nodry < argList.length; nodry++) {

		thisOpt = argList[nodry].trim();
		opt = thisOpt.replace(/^\-+/, '');

		if (opt === thisOpt) {

			// argument value
			if (curOpt) arg[curOpt] = opt;
			curOpt = null;

		} else {

			// argument name
			curOpt = opt;
			arg[curOpt] = true;

		}

 	}

	for (clean = 0; clean < argList.length; clean++) {

		thisOpt = argList[clean].trim();
		opt = thisOpt.replace(/^\-+/, '');

		if (opt === thisOpt) {

			// argument value
			if (curOpt) arg[curOpt] = opt;
			curOpt = null;

		} else {

			// argument name
			curOpt = opt;
			arg[curOpt] = true;

		}
	}

	for (ssh = 0; ssh < argList.length; ssh++) {

		thisOpt = argList[ssh].trim();
		opt = thisOpt.replace(/^\-+/, '');

		if (opt === thisOpt) {

			// argument value
			if (curOpt) arg[curOpt] = opt;
			curOpt = null;

		} else {

			// argument name
			curOpt = opt;
			arg[curOpt] = true;

		}
	}

	return arg;

})(process.argv);

var sshMain = new ssh ({						/* ssh functions with mainserver */
			ignoreErrors: false,
			sshConfig: {
				host: ext_cred.mainserver.hostname,
				port: ext_cred.mainserver.port,
				username: ext_cred.mainserver.username,
				privateKey: fs.readFileSync( ext_cred.mainserver.key )
			}
});

/* Copied/watched files */
var paths = {
	base: {
		src: './src',						/* main lumiere path source */
		dist: './dist',					/* main lumiere path destination */
		watch: './dist/**/*.*',				/* main browsersync watch folder */
		sourcemap: '../tmp/sourcemap',			/* sourcemap output folder */
	},
	stylesheets: {
		src: [
			'./src/**/*.css',
			'!./src/assets/js/highslide/*.*',
			'!./src/vendor/**/*.*'
		],
		dist: './dist'
	},
	javascripts: {
		src: [ 
			'./src/**/*.js',
				'!./src/assets/js/highslide/**/**/*.*',
				'!./src/vendor/**/*.*'
		],
		dist: './dist'
	},
	images: {
		src: [
			'./src/.**/*.{jpg,jpeg,gif,png}', 				/* for .wordpress.org */
			'./src/**/*.{jpg,jpeg,gif,png}',
			'!./src/vendor/**/*.*'
	],
		dist: './dist'
	},
	files: {
		src: [	'./src/**/*.{php,html,htm,ico,webmanifest,md,txt}', 
			'!./src/vendor/**/*.*',				/* copy only relevant files in src/vendor */ 
				'./src/vendor/composer/**/*.*',
				'./src/vendor/jcvignoli/imdbphp/src/**/*.*',
				'./src/vendor/monolog/**/*.*',
				'./src/vendor/psr/**/*.*',
				'./src/vendor/twbs/bootstrap/dist/**/*.{min.js,min.css}',
				'./src/vendor/autoload.*',
			'./src/**/*.+(psd)', 
			'./src/.**/*.+(psd)', 				/* for .wordpress.org */
			'./src/languages/*.*',
				'!./src/languages/*.temp.po',  
			'./src/assets/js/highslide/**/**/*.*'
		],
		dist: './dist'
	},
	rsync: {
		src: './dist',
		excludepath: '.wordpress-org'
	},
};

// Function to check if the var --ssh yes has been passed, or called such as isSSH('yes') (ie; from watch task)
var flagssh = false;
function isSSH( handler ) {
	if ( (arg.ssh == "yes") || (handler == "yes") ) {
		return flagssh = true;
	} 
}

// Task 1 - Minify CSS
gulp.task('stylesheets', () => {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--ssh yes"
	*/
	flagssh = flagssh ? true : isSSH();

	// Notify the user how to run for sshing
	const sshmsg = "** Notice: Run stylesheets with '--ssh yes' for uploading with ssh **";
	if (flagssh != true)
		console.dir( sshmsg );

	return gulp
		.src( paths.stylesheets.src , {base: paths.base.src } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(rename({suffix: '.min'}))
		.pipe(changed( paths.stylesheets.dist ))
		.pipe(autoprefixer('last 2 versions'))
		// Removed class .dropdown-menu in CSS bootstrap.css which breaks OCEANWP
		.pipe(longerif( (file) => file.path.match('bootstrap.min.css'), replace(/(\.dropdown-menu\s\{).+?(border-radius: 0\.25rem;\s\})/s, '')) )
		.pipe(cleanCss({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}`);
			console.log(`${details.name}: ${details.stats.minifiedSize}`);
		}))
		.pipe(gulp.dest( paths.stylesheets.dist ))
		.pipe(longerif(flagssh, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(browserSync.stream())
});

// Task 2 - Minify JS
gulp.task('javascripts', () => {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--ssh yes"
	*/
	flagssh = flagssh ? true : isSSH();

	// Notify the user how to run for sshing
	const sshmsg = "** Notice: Run javascripts with '--ssh yes' for uploading with ssh **";
	if (flagssh != true)
		console.dir( sshmsg );

	return gulp
		.src( paths.javascripts.src , {base: paths.base.src } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & console error msg */
		.pipe(rename({suffix: '.min'}))
		.pipe(changed( paths.javascripts.dist ))
		.pipe(uglify())
		.pipe(gulp.dest( paths.javascripts.dist ))
		.pipe(longerif(flagssh, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(browserSync.stream())
});


// Task 3 - Compress images -> jpg can't be compressed, selecting png and gif only
gulp.task('images', () => {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--ssh yes"
	*/
	flagssh = flagssh ? true : isSSH();

	// Notify the user how to run for sshing
	const sshmsg = "** Notice: Run images with '--ssh yes' for uploading with ssh **";
	if (flagssh != true)
		console.dir( sshmsg );

	return gulp
		.src( paths.images.src, {base: paths.base.src } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(changed( paths.images.dist ))
		.pipe(imagemin())
		.pipe(gulp.dest( paths.images.dist ))
		.pipe(longerif(flagssh, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(browserSync.stream())
});

// Task 4 - Transfer untouched files -> jpg can't be compressed, transfered here
gulp.task('files_copy', () => {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--ssh yes"
	*/
	flagssh = flagssh ? true : isSSH();

	// Notify the user how to run for sshing
	const sshmsg = "** Notice: Run files_copy with '--ssh yes' for uploading with ssh **";
	if (flagssh != true) 
		console.dir( sshmsg );

	return gulp
		.src( paths.files.src, {base: paths.base.src } )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(changed( paths.files.dist ))
		.pipe(gulp.dest( paths.files.dist ))
		.pipe(longerif(flagssh,sshMain.dest( ext_cred.mainserver.dist ) ) )
		.on("error", function (err) { errorHandler(err); console.log("Error:", err); }) /* old way, but maybe need to get an actual ssh error msg? */
		.pipe(browserSync.stream())
});

// Task 5 - Watch files
gulp.task('watch', () => {		/* call tasks with ssh upload by default using var flagssh */
	gulp.watch( paths.stylesheets.src, gulp.series('stylesheets'), flagssh = true);
	gulp.watch( paths.javascripts.src, gulp.series('javascripts'), flagssh = true);
	gulp.watch( paths.images.src, gulp.series('images'), flagssh = true);
	gulp.watch( paths.files.src, gulp.series('files_copy'), flagssh = true);
	console.log('Gulp watch started...');
});

// Task 6 - Run browser-sync
gulp.task('browserWatch', gulp.parallel( 'watch', (done) => {
	browserSync.init({

		// List of options: https://browsersync.io/docs/options

		// Proxy address
		proxy: {
		    target: ext_cred.proxy.address,

		    proxyReq: [
			 function(proxyReq) {
			 	/** Also using in Apache envvars file the option "export APACHE_ARGUMENTS='-D cthulhu'" */
			     proxyReq.setHeader('X-Special-Proxy-Header', 'cthulhu');
			 }
		    ]
		},

		// Don't show any notifications in the browser
		notify:false,

		// port: 8080,

		// Tunnel  the Browsersync server through a Public URL
		// tunnel: true,

		// Additional info about the process, "info", "debug", "warn", or "silent", default: "info"
		// logLevel: "debug",

	});

	gulp.watch( paths.base.watch ).on('change', browserSync.reload);

	done();
}));

// Task 7 - Remove pre-existing content from ./dist folders
gulp.task('cleanDist', (done) => {
	del.sync([
		paths.files.dist
	]);
	done();
});

// Task 8 - Build all files
// @param build 	if the taks is run with "--clean yes" as parameter, run cleanDist first
// 			without that parameter, a notice is displayed in the console

gulp.task('build', function (cb) {

	flagssh = false;

	if (arg.clean == "yes") {
	 	console.dir( 'Deleting ' + paths.files.dist + '...' );
	 	gulp.series( 'cleanDist', gulp.parallel( 'javascripts', 'stylesheets', 'images', 'files_copy' ) )(cb);

	} else {
		var nocleanmsg = '** Notice: Run build with "--clean yes" to clean ' + paths.files.dist + ' before building **';
	 	console.dir( nocleanmsg );
		gulp.series( 'javascripts', 'stylesheets', 'images', 'files_copy' )(cb);
	}
	cb();
});

// Task 9 - Default
//exports.default =  gulp.series('build', 'watch' );
gulp.task('default', () => {
	gulp.series( 'watch' )
});

// Task 10 - Rsync local dist rsynced to mainserver
// @param rsync 	if the taks is run with "--rsync nodry" as parameter, doesn't run with dryrun
// 			without that parameter, dryrun is run and text is displayed in the console+notification
gulp.task('rsync', () => {

	// Notify the user how to run for avoiding a dryrun
	const rsyncmsg = "** Notice: Run with '--nodry yes' for actual syncronization **";
	if (arg.nodry != "yes") {
	 	console.dir( rsyncmsg );
		nodeNotifier.notify({ 
			title: 'Rsync task:', 
			message: rsyncmsg,
			icon: ext_cred.base.gulpimg,
		 });
	}

	return gulp.src( paths.base.dist )
		.pipe(plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(longerif(arg.nodry == "yes", 		/* function without dry-run, correct argument passed */ 
			rsync({
				root: paths.rsync.src,
				hostname: ext_cred.mainserver.hostname,
				destination: ext_cred.mainserver.dist,
				username: ext_cred.mainserver.username,
				options: {
					'e': 'ssh -i ' + ext_cred.mainserver.key
				},
				recursive: true,
				incremental: true,
				progress: true,
				compress: true,
				clean: true,
				exclude: paths.rsync.excludepath
			})
		))
		.pipe(longerif(arg.nodry != "yes", 		/* function with dry-run, no argument passed */
			rsync({
				root: paths.rsync.src,
				hostname: ext_cred.mainserver.hostname,
				destination: ext_cred.mainserver.dist,
				username: ext_cred.mainserver.username,
				options: {
					'e': 'ssh -i ' + ext_cred.mainserver.key
				},
				recursive: true,
				incremental: true,
				progress: true,
				clean: true,
				dryrun: true,
				compress: true,
				exclude: [ paths.rsync.excludepath ]
			})
		))
});

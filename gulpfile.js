/** 
 * Infomaniak root website workflow
 *
 * Changed files are directly uploaded to the main server by ssh
 * Rsync available to syncronize it all
 * Errors notified (notify)
 * Can use external parameters to modify tasks behaviour (--clean yes, --ssh yes)
 * Copying taks must be run --ssh yes to upload to ssh external server
 */

import gulp from 'gulp';
import browserSync from 'browser-sync';
import plumber from 'gulp-plumber';
import notify from 'gulp-notify';
import longerif from 'gulp-if';
import ssh from 'gulp-ssh';
import replace from 'gulp-replace';
import autoprefixer from 'gulp-autoprefixer';
import cleanCss from 'gulp-clean-css';
import changed from 'gulp-changed';
import rename from 'gulp-rename';
import terser from 'gulp-terser';
import imagemin from 'gulp-imagemin';
import fs from 'fs-extra';
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

// Constant to get from the command-line "--ssh yes" for running building tasks with ssh upload
var arg = (argList => {

	let arg = {}, ssh, opt, thisOpt, curOpt;

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

var sshMain = new ssh ({					/* ssh functions with mainserver */
	ignoreErrors: false,
	sshConfig: {
		host: ext_cred.mainserver.hostname,
		port: ext_cred.mainserver.port,
		username: ext_cred.mainserver.username,
		privateKey: fs.readFileSync( ext_cred.mainserver.key ),
		/* debug: console.log, */
	}
});

/* Copied/watched files */
var paths = {
	base: {
		src: './src',					/* main lumiere path source */
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
			'./src/.**/*.{jpg,jpeg,gif,png}', 				/* extra pics for .wordpress.org */
			'./src/**/*.{jpg,jpeg,gif,png}',
			'!./src/vendor/**/*.*'
	],
		dist: './dist'
	},
	files: {
		src: [	'./src/**/*.{php,html,htm,ico,webmanifest,md,txt,json}', 

			/* Remove irrelevant files in src/vendor */ 
			'!./src/vendor/bin/*.*',				
			'!./src/vendor/jcvignoli/imdbphp/cache/**/*.*',
			'!./src/vendor/jcvignoli/imdbphp/conf/**/*.*',
			'!./src/vendor/jcvignoli/imdbphp/demo/**/*.*',
			'!./src/vendor/jcvignoli/imdbphp/doc/**/*.*',
			'!./src/vendor/jcvignoli/imdbphp/graphql/**/*.*',
			'!./src/vendor/jcvignoli/imdbphp/tests/**/*.*',
			'!./src/vendor/jcvignoli/imdbphp/.github/**/*.*',
			'!./src/vendor/jcvignoli/imdbphp/.github/*.*',
			'!./src/vendor/jcvignoli/imdbphp/*.*',
			'!./src/vendor/twbs/bootstrap/build/**/*.*',
			'!./src/vendor/twbs/bootstrap/js/**/*.*',
			'!./src/vendor/twbs/bootstrap/nuget/**/*.*',
			'!./src/vendor/twbs/bootstrap/scss/**/*.*',
			'!./src/vendor/twbs/bootstrap/site/**/*.*',
			'!./src/vendor/twbs/bootstrap/.github/**/*.*',
			'!./src/vendor/twbs/bootstrap/*.*',

			'./src/**/*.+(psd)', 
			'./src/.**/*.{psd,json}',	 				/* extra files for .wordpress.org -- doesn't work for blueprints */
			'./src/languages/*.*',
				'!./src/languages/*.temp.po',  
			'./src/assets/js/highslide/**/**/*.*'
		],
		dist: './dist'
	}
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
		.pipe(terser())
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
		.src( paths.images.src, {base: paths.base.src, encoding: false } )
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
		notify:true,

		// port: 8080,

		// Tunnel  the Browsersync server through a Public URL
		// tunnel: true,

		// Additional info about the process, "info", "debug", "warn", or "silent", default: "info"
		// logLevel: "debug",
		
		// reloadDelay: 8000, // The process of copying is slow, so need to wait until src/ is copied to dist/ then reload server

	});

	gulp.watch( paths.base.watch ).on('change', browserSync.reload);

	done();
}));

// Task 7 - Default
gulp.task('default', () => {
	gulp.series( 'watch' )
});

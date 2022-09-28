/** 
 * <Lumière WordPress plugin workflow>
 * When "build" Files are concatened, minified, copied from src to dist, then uploaded to the main server by ssh
 * Rsync available to syncronize it all
 * Errors notified (notify)
 * Can use external parameters to modify tasks behaviour (--clean yes, --nodry yes, --ssh yes)
 * Using gulp-load-plugins to load plugins on demand
 * Copying taks must be run --ssh yes to upload to ssh external server
 */

var plugins = require("gulp-load-plugins")({			/* Autoload all gulp plugins in pattern */
	/*DEBUG: true,*/
	camelize: true,
	overridePattern: true,					/* option to add a new pattern and include functions
									not starting with gulp- */
	pattern: ['gulp-*', 'gulp.*', '@*/gulp{-,.}*', 'fs', 'browser-sync', 'del', 'node-notifier']
});

/* Require gulp packages */
var gulp = 	require('gulp'),
/*gulpplugins	replace = require('gulp-replace'),			/* replace string in file */
/*gulpplugins	browserSync = require('browser-sync'),		/* open a proxy browser tab, auto refresh on files edit */
/*gulpplugins	cleanCSS = require('gulp-clean-css'),		/* minify css */		
/*gulpplugins	autoprefixer = require('gulp-autoprefixer'),	/* adds support for old browsers in CSS */
/*gulpplugins	plumber = require('gulp-plumber'),			/* avoid running process that breaks when error */
/*gulpplugins	js = require('gulp-uglify'),			/* minify javascripts */
/*gulpplugins	changed = require('gulp-changed'),			/* check if a file has changed */
/*gulpplugins	imagemin = require('gulp-imagemin'),		/* compress images */
/*gulpplugins	eslint = require("gulp-eslint"),			/* check if javascript is correctly written */
/*gulpplugins	notify = require('gulp-notify'),			/* add notification OSD system (needs notify-osd) */
/*gulpplugins	del = require('del'),				/* delete files */
/*gulpplugins	shell = require('gulp-shell'),			 execute shell functions 
										example: .pipe(shell(['echo <%= file.path %>'])) */
/*gulpplugins	if = require('gulp-if'),				/* if function */
/*gulpplugins	rename = require('gulp-rename'),			/* rename function */
/*gulpplugins	ssh = require('gulp-ssh'),				 ssh functions */
/*gulpplugins	fs = require ('fs'),					/* filesystem functions */
/*gulpplugins	rsync = require('gulp-rsync'),			/* rsync functions */
/*gulpplugins	nodeNotifier = require('node-notifier'),		/* Notify functions to be run outside a pipe */
ext_cred = require( './.gulpcredentials.js' );			/* private credentials for ssh */


var errorHandler = function(error) {				/* handle and display errors with notify */
	plugins.notify.onError({
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

var 		sshMain = new plugins.ssh ({					/* ssh functions with mainserver */
			ignoreErrors: false,
			sshConfig: {
				host: ext_cred.mainserver.hostname,
				port: ext_cred.mainserver.port,
				username: ext_cred.mainserver.username,
				privateKey: plugins.fs.readFileSync( ext_cred.mainserver.key )
			}
		})

/* Copied/watched files */
paths = {
	base: {
		src: './src',						/* main lumiere path source */
		dist: './dist',					/* main lumiere path destination */
		watch: './dist/**/*.*',				/* main browsersync watch folder */
		sourcemap: '../tmp/sourcemap',			/* sourcemap output folder */
		lint: './tmp/lint' },				/* lint output folder */
	stylesheets: {
		src: [ './src/**/*.css', '!./src/js/highslide/*.*', '!./src/vendor/**/*.*' ],
		dist: './dist' },
	javascripts: {
		src: [ './src/**/*.js', '!./src/js/highslide/**/**/*.*', '!./src/vendor/**/*.*' ],
		dist: './dist' },
	images: {
		src: [ './src/.**/*.+(jpg|jpeg|gif|png)', './src/**/*.+(jpg|jpeg|gif|png)', '!./src/vendor/**/*.*' ],
		dist: './dist' },
	files: {
		src: [	'./src/**/*.+(php|html|htm|ico|webmanifest|md|txt)', 

			'!./src/vendor/**/*.*',				/* copy only relevant files in src/vendor */ 
			'./src/vendor/composer/**/*.*',
			'./src/vendor/imdbphp/imdbphp/src/**/*.*',
			'./src/vendor/monolog/**/*.*',
			'./src/vendor/psr/**/*.*',
			'./src/vendor/twbs/bootstrap/dist/**/*.+(js|css)',
			'./src/vendor/autoload.*',

			'./src/**/*.+(psd)', 
			'./src/.**/*.+(psd)', 				/* for .wordpress.org */
			'./src/languages/*.*', 
			'./src/js/highslide/**/**/*.*'],
		dist: './dist' },
	rsync: {
		src: './dist',
		excludedpath: ''},
};

// Function to check if the var --ssh yes has been passed, or called such as isSSH('yes') (ie; from watch task)
var flagssh = false;
function isSSH( handler ) {
	if ( (arg.ssh == "yes") || (handler == "yes") ) {
		return flagssh = true;
	} 
}

// Task 1 - Minify CSS
exports.stylesheets = function stylesheets() {

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
		.pipe(plugins.plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(plugins.rename({suffix: '.min'}))
		.pipe(plugins.changed( paths.stylesheets.dist ))
		.pipe(plugins.autoprefixer('last 2 versions'))
		// Removed class .dropdown-menu in CSS bootstrap.css which breaks OCEANWP
		.pipe(plugins.if ( (file) => file.path.match('bootstrap.min.css'), plugins.replace(/(\.dropdown-menu\s\{).+?(border-radius: 0\.25rem;\s\})/s, '')) )
		.pipe(plugins.cleanCss({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}`);
			console.log(`${details.name}: ${details.stats.minifiedSize}`);
		}))
		.pipe(gulp.dest( paths.stylesheets.dist ))
		.pipe(plugins.if(flagssh, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(plugins.browserSync.stream())
};

// Task 2 - Minify JS
exports.javascripts = function javascripts() {

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
		.pipe(plugins.plumber( function (err) { errorHandler(err) })) /* throws a popup & console error msg */
		.pipe(plugins.rename({suffix: '.min'}))
		.pipe(plugins.changed( paths.javascripts.dist ))
		.pipe(plugins.uglify())
		.pipe(gulp.dest( paths.javascripts.dist ))
		.pipe(plugins.if(flagssh, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(plugins.browserSync.stream())
};


// Task 3 - Compress images -> jpg can't be compressed, selecting png and gif only
exports.images = function images() {

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
		.pipe(plugins.plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(plugins.changed( paths.images.dist ))
		.pipe(plugins.imagemin())
		.pipe(gulp.dest( paths.images.dist ))
		.pipe(plugins.if(flagssh, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(plugins.browserSync.stream())
};

// Task 4 - Transfer untouched files -> jpg can't be compressed, transfered here
exports.files_copy = function files_copy() {

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
		.pipe(plugins.plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(plugins.changed( paths.files.dist ))
		.pipe(gulp.dest( paths.files.dist ))
		.pipe(plugins.if(flagssh,sshMain.dest( ext_cred.mainserver.dist ) ) )
		.on("error", function (err) { errorHandler(err); console.log("Error:", err); }) /* old way, but maybe need to get an actual ssh error msg? */
		.pipe(plugins.browserSync.stream())
};

// Task 5 - Watch files
gulp.task('watch', function(){			/* call tasks with ssh upload by default using var flagssh */
//old	gulp.watch( paths.files.src, gulp.parallel( function() { flagssh = true;}, 'stylesheets' ) );
	gulp.watch( paths.stylesheets.src, gulp.series('stylesheets'), flagssh = true);
	gulp.watch( paths.javascripts.src, gulp.series('javascripts'), flagssh = true);
	gulp.watch( paths.images.src, gulp.series('images'), flagssh = true);
	gulp.watch( paths.files.src, gulp.series('files_copy'), flagssh = true)  ;
});

// Task 6 - Run browser-sync
gulp.task('browserWatch', gulp.parallel( 'watch', function(done){

	plugins.browserSync.init({

		// List of options: https://browsersync.io/docs/options

		// Proxy address
		proxy: {
		    target: ext_cred.proxy.address,
			/*
		    proxyReq: [
			 function(proxyReq) {
			     proxyReq.setHeader('X-Special-Proxy-Header', 'foobar');
			 }
		    ]*/
		},

		// Don't show any notifications in the browser
		notify:false,

		// port: 8080,

		// Tunnel  the Browsersync server through a Public URL
		// tunnel: true,

		// Additional info about the process, "info", "debug", "warn", or "silent", default: "info"
		// logLevel: "debug",

	});

	gulp.watch( paths.base.watch ).on('change', plugins.browserSync.reload);

	done();
}));

// Task 7 - Remove pre-existing content from ./dist folders
exports.cleanDist = function cleanDist(done) {
	plugins.del.sync([
		paths.files.dist
	]);
	done();
};

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
})

// Task 9 - Default
exports.default =  gulp.series('build', 'watch' );

// Task 10 - Lint
// Check correct javascript writing
function isFixed(file) {
    // Has ESLint fixed the file contents?
    return file.eslint != null && file.eslint.fixed;
}
exports.lint = function lint(cb) {

	return gulp    
		.src( paths.javascripts.src )
		.pipe(plugins.plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		// eslint() attaches the lint output to the "eslint" property
		// of the file object so it can be used by other modules.
		.pipe(plugins.eslint({fix:true}))
		.pipe(plugins.eslint.format())
		// if fixed, write the file to dest
		.pipe(plugins.if(isFixed, gulp.dest( paths.base.lint )))
		// To have the process exit with an error code (1) on
		// lint error, return the stream and pipe to failAfterError 
		// last.
		.pipe(plugins.eslint.failAfterError())
};

// Task 10 - Rsync local dist rsynced to mainserver
// @param rsync 	if the taks is run with "--rsync nodry" as parameter, doesn't run with dryrun
// 			without that parameter, dryrun is run and text is displayed in the console+notification

exports.rsync = function rsync() {

	// Notify the user how to run for avoiding a dryrun
	const rsyncmsg = "** Notice: Run with '--nodry yes' for actual syncronization **";
	if (arg.nodry != "yes") {
	 	console.dir( rsyncmsg );
		plugins.nodeNotifier.notify({ 
			title: 'Rsync task:', 
			message: rsyncmsg,
			icon: ext_cred.base.gulpimg,
		 });
	}

	return gulp.src( paths.base.dist )
		.pipe(plugins.plumber( function (err) { errorHandler(err) })) /* throws a popup & consold error msg */
		.pipe(plugins.if(arg.nodry == "yes", 		/* function without dry-run, correct argument passed */ 
			plugins.rsync({
				root: paths.rsync.src,
				hostname: ext_cred.mainserver.hostname,
				destination: ext_cred.mainserver.dist,
				recursive: true,
				incremental: true,
				progress: true,
				compress: true,
				clean: true,
				exclude: [ paths.rsync.excludepath ]
			})
		))
		.pipe(plugins.if(arg.nodry != "yes", 		/* function with dry-run, no argument passed */
			plugins.rsync({
				root: paths.rsync.src,
				hostname: ext_cred.mainserver.hostname,
				destination: ext_cred.mainserver.dist,
				recursive: true,
				incremental: true,
				progress: true,
				clean: true,
				dryrun: true,
				compress: true,
				exclude: [ paths.rsync.excludepath ]
			})
		))
};


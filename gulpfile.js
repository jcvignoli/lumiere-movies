/**** Lumière WordPress plugin workflow
** When "build" Files are concatened, minified, copied from src to dist, then uploaded to the main server by ssh
** Rsync available to syncronize it all
** Errors notified (notify)
** Can use external parameters to modify tasks behaviour (--clean yes, --rsyncnodry yes, --withssh yes)
** Using gulp-load-plugins to load plugins on demand
** Copying taks must be run --withssh yes to upload to ssh external server
**/

var plugins = require("gulp-load-plugins")({
	/*DEBUG: true,*/
	camelize: true,
	overridePattern: true,					/* option to add a new pattern and include functions
									not starting with gulp- */
	pattern: ['gulp-*', 'gulp.*', '@*/gulp{-,.}*', 'fs', 'browser-sync', 'del', 'node-notifier']
});

/* Require gulp packages */
var gulp = 	require('gulp'),
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
/*gulpplugins	ssh = require('gulp-ssh'),				 ssh functions */
/*gulpplugins	fs = require ('fs'),					/* filesystem functions */
/*gulpplugins	rsync = require('gulp-rsync'),			/* rsync functions */
/*gulpplugins	nodeNotifier = require('node-notifier'),		/* Notify functions to be run outside a pipe */
		ext_cred = require( './.gulpcredentials.js' );	/* private credentials for ssh */


var errorHandler = function(error) {				/* handle and display errors with notify */
	plugins.notify.onError({
		title: 'Task Failed [' + error.plugin + ']',
		message: error.message,
		sound: true
	})(error);
	this.emit('end');
};

// Constant to get from the command-line "--rsync nodry" for rsync task or "--build clean" for build task or "--withssh yes" for running building tasks with ssh upload
var arg = (argList => {

	let arg = {}, rsyncnodry, clean, withssh, opt, thisOpt, curOpt;

	for (rsyncnodry = 0; rsyncnodry < argList.length; rsyncnodry++) {

		thisOpt = argList[rsyncnodry].trim();
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

	for (withssh = 0; withssh < argList.length; withssh++) {

		thisOpt = argList[withssh].trim();
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
		watch: './dist/**/*.*' },				/* main browsersync watch folder */
	stylesheets: {
		src: [ './src/**/*.css', '!./src/js/highslide/*.*' ],
		dist: './dist' },
	javascripts: {
		src: [ './src/**/*.js', '!./src/js/highslide/**/**/*.*' ],
		dist: './dist' },
	images: {
		src: [ './src/.**/*.+(jpg|jpeg|gif|png)', './src/**/*.+(jpg|jpeg|gif|png)' ],
		dist: './dist' },
	files: {
		src: [	'./src/**/*.php', 
			'./src/**/*.ico', 
			'./src/**/*.webmanifest', 
			'./src/**/*.+(psd)', 
			'./src/.**/*.+(psd)', 				/* for .wordpress.org */
			'./src/**/*.txt', 
			'./src/**/*.md', 
			'./src/languages/*.*', 
			'./src/js/highslide/**/**/*.*'],
		dist: './dist' },
	rsync: {
		src: './dist',
		excludedpath: ''},
	lint: {
		output: './tmp/lint' },					/* lint output  folder */
};

// Function to check if the var --withssh yes has been passed, or called such as isSSH('yes') (ie; from watch task)
var flagssh = false;
function isSSH( handler ) {
	if ( (arg.withssh == "yes") || (handler == "yes") ) {
		return flagssh = true;
	} 
}

// Task 1 - Minify CSS
gulp.task('stylesheets', function (cb) {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--withssh yes"
	*/
	flagssh = flagssh ? flagssh : isSSH();

	// Notify the user how to run for sshing
	const withsshmsg = "** Notice: Run stylesheets with --withssh 'yes' for uploading with ssh **";
	if (flagssh != true)
		console.dir( withsshmsg );

	return gulp
		.src( paths.stylesheets.src , {base: paths.base.src } )
		.pipe(plugins.changed( paths.stylesheets.dist ))
		.pipe(plugins.autoprefixer('last 2 versions'))
		.pipe(plugins.cleanCss({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}`);
			console.log(`${details.name}: ${details.stats.minifiedSize}`);
		}))
		.pipe(gulp.dest( paths.stylesheets.dist ))
		.pipe(plugins.if(flagssh == true, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(plugins.browserSync.stream())
		.on("error", errorHandler)
		.on("error", function (err) { console.log("Error:", err); })
	cb();
});

// Task 2 - Minify JS
gulp.task('javascripts', function () {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--withssh yes"
	*/
	flagssh = flagssh ? flagssh : isSSH();
	if (flagssh != true)
		console.dir( withsshmsg );

	// Notify the user how to run for sshing
	const withsshmsg = "** Notice: Run javascripts with --withssh 'yes' for uploading with ssh **";

	return gulp
		.src( paths.javascripts.src , {base: paths.base.src } )
		.pipe(plugins.changed( paths.javascripts.dist ))
		.pipe(plugins.uglify())
		.pipe(gulp.dest( paths.javascripts.dist ))
		.pipe(plugins.if(flagssh == true, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(plugins.browserSync.stream())
		.on("error", errorHandler)
		.on("error", function (err) { console.log("Error:", err); })
});

// Task 3 - Compress images -> jpg can't be compressed, selecting png and gif only
gulp.task('images', function () {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--withssh yes"
	*/
	flagssh = flagssh ? flagssh : isSSH();

	// Notify the user how to run for sshing
	const withsshmsg = "** Notice: Run images with --withssh 'yes' for uploading with ssh **";
	if (flagssh != true)
		console.dir( withsshmsg );

	return gulp
		.src( paths.images.src, {base: paths.base.src } )
		.pipe(plugins.changed( paths.images.dist ))
		.pipe(plugins.imagemin())
		.pipe(gulp.dest( paths.images.dist ))
		.pipe(plugins.if(flagssh == true, sshMain.dest( ext_cred.mainserver.dist )))
		.pipe(plugins.browserSync.stream())
		.on("error", errorHandler)
		.on("error", function (err) { console.log("Error:", err); })
});

// Task 4 - Transfer untouched files -> jpg can't be compressed, transfered here
gulp.task('files_copy', function() {

	/* Set the flag to whether do ssh upload or not to: 
		1/ if flagssh exists, takes its current value; (ie: call with function in gulp.watch)
		2/ if flagssh doesn't exists, check if the files_copy task was called with "--withssh yes"
	*/
	flagssh = flagssh ? flagssh : isSSH();

	// Notify the user how to run for sshing
	const withsshmsg = "** Notice: Run files_copy with --withssh 'yes for uploading with ssh **";
	if (flagssh != true) 
		console.dir( withsshmsg );

	return gulp
		.src( paths.files.src, {base: paths.base.src } )
		.pipe(plugins.plumber({ errorHandler: function(err) {
		     plugins.notify.onError({
			  title: "Gulp error in " + err.plugin,
			  message:  err.toString()
		     })(err);
		 }}))
		.pipe(plugins.changed( paths.files.dist ))
		.pipe(gulp.dest( paths.files.dist ))
		.pipe(plugins.if(flagssh == true,sshMain.dest( ext_cred.mainserver.dist ) ) )
		.on('ssh2Data', function(data){ console.dir( "test"+data.toString() ) } ) // supposed to return ssh errors
		.pipe(plugins.browserSync.stream())
		.on("error", errorHandler)
		.on("error", function (err) { console.log("Error:", err); })
});

// Task 5 - Watch files
gulp.task('watch', function(){			/* call tasks with ssh upload by default using var flagssh */
//old	gulp.watch( paths.files.src, gulp.parallel( function() { flagssh = true;}, 'stylesheets' ) );
	gulp.watch( paths.stylesheets.src, gulp.series('stylesheets'), flagssh = true);
	gulp.watch( paths.javascripts.src, gulp.series('javascripts'), flagssh = true);
	gulp.watch( paths.images.src, gulp.series('images'), flagssh = true) ;
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

// Task 7 - Remove pre-existing content from output folders
gulp.task('cleanDist', function (done) {
	plugins.del.sync([
		paths.files.dist
	]);
	done();
});

// Task 8 - Build all files
// @param build 	if the taks is run with "--clean yes" as parameter, run cleanDist first
// 			without that parameter, a notice is displayed in the console

gulp.task('build', function (cb) {

	if (arg.clean != "yes") {
	 	console.dir( '** Notice: Run build with --clean "yes" to clean ' + paths.files.dist + ' before building **' );
		gulp.series( 'javascripts', 'stylesheets', 'images', 'files_copy' )(cb);
	} else {
	 	console.dir( 'Deleting ' + paths.files.dist + '...' );
	 	gulp.series( 'cleanDist', gulp.parallel( 'javascripts', 'stylesheets', 'images', 'files_copy' ) )(cb);
	}
	return true;
})

// Task 9 - Default
gulp.task('default', gulp.series('build', 'watch' ) );

// Task 10 - Lint
// Check correct javascript writing
function isFixed(file) {
    // Has ESLint fixed the file contents?
    return file.eslint != null && file.eslint.fixed;
}
gulp.task('lint', function(cb) {
	return gulp    
		.src( paths.javascripts.src )
		// eslint() attaches the lint output to the "eslint" property
		// of the file object so it can be used by other modules.
		.pipe(plugins.eslint({fix:true}))
		.pipe(plugins.eslint.format())
		// if fixed, write the file to dest
		.pipe(plugins.if(isFixed, gulp.dest( paths.lint.output )))
		// To have the process exit with an error code (1) on
		// lint error, return the stream and pipe to failAfterError 
		// last.
		.pipe(plugins.eslint.failAfterError())
		.on("error", errorHandler)
		.on("error", function (err) { console.log("Error:", err); })
});

// Task 10 - Rsync local dist rsynced to mainserver
// @param rsync 	if the taks is run with "--rsync nodry" as parameter, doesn't run with dryrun
// 			without that parameter, dryrun is run and text is displayed in the console+notification
gulp.task('rsync', function(){

	// Notify the user how to run for avoiding a dryrun
	const rsyncmsg = "** Notice: Run with --rsyncnodry 'yes' for actual syncronization **";
	if (arg.rsyncnodry != "yes") {
	 	console.dir( rsyncmsg );
		plugins.nodeNotifier.notify({ 
			title: 'Rsync task:', 
			message: rsyncmsg,
			icon: './source/gulp.png',
		       templateOptions: {
        			date: new Date()
      			}
		 });
	}

	return gulp.src( paths.base.dist )
		/* notify error with plumber, but I don't use plumber anymore */
/*		.pipe(plugins.plumber({ errorHandler: function(err) {
		     plugins.notify.onError({
			  title: "Gulp error in " + err.plugin,
			  message:  err.toString()
		     })(err);
		 }}))
*/
		.pipe(plugins.if(arg.rsyncnodry == "yes", 		/* function without dry-run, correct argument passed */ 
			plugins.rsync({
				root: paths.rsync.src,
				hostname: ext_cred.mainserver.hostname,
				destination: ext_cred.mainserver.dist,
				recursive: true,
				incremental: true,
				progress: true,
				compress: true,
				exclude: [ paths.rsync.excludepath ]
			})
		))
		.pipe(plugins.if(arg.rsyncnodry != "yes", 		/* function with dry-run, no argument passed */
			plugins.rsync({
				root: paths.rsync.src,
				hostname: ext_cred.mainserver.hostname,
				destination: ext_cred.mainserver.dist,
				recursive: true,
				incremental: true,
				progress: true,
				dryrun: true,
				compress: true,
				exclude: [ paths.rsync.excludepath ]
			})
		))
		.on("error", errorHandler)
		.on("error", function (err) { console.log("Error:", err); })
});


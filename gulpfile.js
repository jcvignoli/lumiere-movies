/* Require gulp packages */
var gulp = 	require('gulp'),
		browserSync = require('browser-sync'),		/* open a proxy browser tab, auto refresh on files edit */
		reload = browserSync.reload,
		cleanCSS = require('gulp-clean-css'),		/* minify css */		
		autoprefixer = require('gulp-autoprefixer'),	/* adds support for old browsers in CSS */
		plumber = require('gulp-plumber'),			/* avoid running process to break for an error */
		js = require('gulp-uglify'),			/* minify javascripts */
		changed = require('gulp-changed'),			/* check if a file has changed */
		imagemin = require('gulp-imagemin'),		/* compress images */
		eslint = require("gulp-eslint"),			/* check if javascript is correctly written */
		notify = require('gulp-notify'),			/* add notification OSD system (needs notify-osd) */
		del = require('del'),				/* delete files */
		gulpIf = require('gulp-if');			/* add if function */

/* Copied/watched files */
paths = {
	stylesheets: {
		src: [ './src/**/*.css', '!./src/js/highslide/*.*' ],
		dist: './dist' },
	javascripts: {
		src: [ './src/**/*.js', '!./src/js/highslide/**/**/*.*' ],
		dist: './dist' },
	images: {
		src: [ './src/.**/*.*', './src/**/*.*' ],
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
		dist: './dist' }
};


// Task 1 - Minify CSS
gulp.task('stylesheets', function () {
	return gulp
		.src( paths.stylesheets.src , {base: './src'} )
		.pipe(plumber({ errorHandler: function(err) {
		     notify.onError({
			  title: "Gulp error in " + err.plugin,
			  message:  err.toString()
		     })(err);
		 }}))
		.pipe(changed( paths.stylesheets.dist ))
		.pipe(autoprefixer('last 2 versions'))
		.pipe(cleanCSS({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}`);
			console.log(`${details.name}: ${details.stats.minifiedSize}`);
		}))
		.pipe(gulp.dest( paths.stylesheets.dist ))
		.pipe(browserSync.stream())
});

// Task 2 - Minify JS
gulp.task('javascripts', function () {
	return gulp
		.src( paths.javascripts.src , {base: './src'} )
		.pipe(plumber({ errorHandler: function(err) {
		     notify.onError({
		         title: "Gulp error in " + err.plugin,
		         message:  err.toString()
		     })(err);
		 }}))
		.pipe(changed( paths.javascripts.dist ))
		.pipe(js())
		.pipe(gulp.dest( paths.javascripts.dist ))
		.pipe(browserSync.stream());
});

// Task 3 - Compress images -> jpg can't be compressed, selecting png and gif only
gulp.task('images', function () {
	return gulp
		.src( paths.images.src, {base: './src'} )
		.pipe(plumber({ errorHandler: function(err) {
		     notify.onError({
			  title: "Gulp error in " + err.plugin,
			  message:  err.toString()
		     })(err);
		 }}))
		.pipe(changed( paths.images.dist ))
		.pipe(imagemin())
		.pipe(gulp.dest( paths.images.dist ))
		.pipe(browserSync.stream());
});

// Task 4 - Transfer untouched files -> jpg can't be compressed, transfered here
gulp.task('files_copy', function () {
	return gulp
		.src( paths.files.src, {base: './src'} )
		.pipe(plumber({ errorHandler: function(err) {
		     notify.onError({
			  title: "Gulp error in " + err.plugin,
			  message:  err.toString()
		     })(err);
		 }}))
		.pipe(changed( paths.files.dist ))
		.pipe(gulp.dest( paths.files.dist ))
		.pipe(browserSync.stream());
});

// Task 5 - Watch files
gulp.task('watch', function(){
	gulp.watch( paths.stylesheets.src, gulp.series('stylesheets') );
	gulp.watch( paths.javascripts.src,  gulp.series('javascripts') );
	gulp.watch( paths.images.src,  gulp.series('images') );
	gulp.watch( paths.files.src , gulp.series('files_copy') );
});

// Task 6 - Run browser-sync
gulp.task('browserWatch', gulp.parallel( 'watch', function(done){
	browserSync.init({
		proxy: 'local.lumiere/website/',
		notify:false
	});
	gulp.watch( './dist/**/*.*' ).on('change', browserSync.reload);
	done();
}));

// Task 7 - Remove pre-existing content from output folders
gulp.task('cleanDist', function () {
	del.sync([
		paths.files.dist
	]);
});

// Task 8 - Build all files
gulp.task('build', gulp.series( 'javascripts', 'stylesheets', 'images', 'files_copy' ) );

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
		.pipe(plumber({ errorHandler: function(err) {
		     notify.onError({
			  title: "Gulp error in " + err.plugin,
			  message:  err.toString()
		     })(err);
		 }}))
		.pipe(eslint({fix:true}))
		.pipe(eslint.format())
		// if fixed, write the file to dest
		.pipe(gulpIf(isFixed, gulp.dest('./tmp/lint')))
		// To have the process exit with an error code (1) on
		// lint error, return the stream and pipe to failAfterError 
		// last.
		.pipe(eslint.failAfterError());
});


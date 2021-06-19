/* Require gulp packages */
var gulp = 	require('gulp'),
		browserSync = require('browser-sync'),
		reload = browserSync.reload,
		cleanCSS = require('gulp-clean-css');
		autoprefixer = require('gulp-autoprefixer'),
		plumber = require('gulp-plumber');
		js = require('gulp-uglify'),
		changed = require('gulp-changed');
		imagemin = require('gulp-imagemin');

/* Copied/watched files */
var stylesheets_paths =[ './src/**/*.css', '!./src/js/highslide/*.*' ];
var javascripts_paths = [ './src/**/*.js', '!./src/js/highslide/**/**/*.*' ];
var images_paths = [ './src/.wordpress-org/*.+(png|gif)', './src/pics/**/*.+(png|gif)' ];
var files_copy_paths = [	'./src/**/*.php', 
				'./src/**/*.ico', 
				'./src/**/*.webmanifest', 
				'./src/**/*.+(jpg|jpeg|psd)', 
				'./src/.**/*.+(jpg|jpeg|psd)', 
				'./src/**/*.txt', 
				'./src/**/*.md', 
				'./src/languages/*.*', 
				'./src/js/highslide/**/**/*.*'
			];

// Task 1 - Run browser-sync
gulp.task('browserWatch', function() {
	browserSync.init({
		proxy: 'local.lumiere/website/',
		notify:false
	});
	gulp.watch( './dist/**/*.*' ).on('change', browserSync.reload);
});

// Task 2 - Minify CSS
gulp.task('stylesheets', function () {
	return gulp.src( stylesheets_paths , {base: './src'} )
		.pipe(changed('./dist'))
		.pipe(plumber())
		.pipe(autoprefixer('last 2 versions'))
		.pipe(cleanCSS({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}`);
			console.log(`${details.name}: ${details.stats.minifiedSize}`);
		}))
		.pipe(gulp.dest('./dist'))
		.pipe(browserSync.stream())
});

// Task 3 - Minify JS
gulp.task('javascripts', function () {
	return gulp.src( javascripts_paths, {base: './src'} )
		.pipe(changed('./dist'))
		.pipe(js())
		.pipe(gulp.dest('./dist'))
		.pipe(browserSync.stream());
});

// Task 4 - Compress images -> jpg can't be compressed, selecting png and gif only
gulp.task('images', function () {
	return gulp.src( images_paths, {base: './src'} )
		.pipe(changed('./dist'))
		.pipe(imagemin())
		.pipe(gulp.dest('./dist'))
		.pipe(browserSync.stream());
});

// Task 5 - Transfer untouched files -> jpg can't be compressed, transfered here
gulp.task('files_copy', function () {
	return gulp.src( files_copy_paths, {base: './src'} )
		.pipe(changed('./dist'))
		.pipe(gulp.dest('./dist'))
		.pipe(browserSync.stream());
});

// Task 6 - Watch files
gulp.task('watch', gulp.parallel( 'browserWatch', function(done){
	gulp.watch( stylesheets_paths, gulp.series('stylesheets') );
	gulp.watch( javascripts_paths,  gulp.series('javascripts') );
	gulp.watch( images_paths,  gulp.series('images') );
	gulp.watch( files_copy_paths , gulp.series('files_copy') );
	done();
}));

// Task 7 - Build all files
gulp.task('build', gulp.parallel('javascripts', 'stylesheets', 'images', 'files_copy' ) );

// Task 8 - Default
gulp.task('default', gulp.series('build', 'watch' ) );


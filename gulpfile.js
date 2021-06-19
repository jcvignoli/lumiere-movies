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
paths = {
	stylesheets: {
		src: [ './src/**/*.css', '!./src/js/highslide/*.*' ],
		dist: './dist' },
	javascripts: {
		src: [ './src/**/*.js', '!./src/js/highslide/**/**/*.*' ],
		dist: './dist' },
	images: {
		src: [ './src/.wordpress-org/*.+(png|gif)', './src/pics/**/*.+(png|gif)' ],
		dist: './dist' },
	files: {
		src: [	'./src/**/*.php', 
			'./src/**/*.ico', 
			'./src/**/*.webmanifest', 
			'./src/**/*.+(jpg|jpeg|psd)', 
			'./src/.**/*.+(jpg|jpeg|psd)', 
			'./src/**/*.txt', 
			'./src/**/*.md', 
			'./src/languages/*.*', 
			'./src/js/highslide/**/**/*.*'],
		dist: './dist' }
};


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
	return gulp.src( paths.stylesheets.src , {base: './src'} )
		.pipe(changed( paths.stylesheets.dist ))
		.pipe(plumber())
		.pipe(autoprefixer('last 2 versions'))
		.pipe(cleanCSS({debug: true}, (details) => {
			console.log(`${details.name}: ${details.stats.originalSize}`);
			console.log(`${details.name}: ${details.stats.minifiedSize}`);
		}))
		.pipe(gulp.dest( paths.stylesheets.dist ))
		.pipe(browserSync.stream())
});

// Task 3 - Minify JS
gulp.task('javascripts', function () {
	return gulp.src( paths.javascripts.src , {base: './src'} )
		.pipe(changed( paths.javascripts.dist ))
		.pipe(js())
		.pipe(gulp.dest( paths.javascripts.dist ))
		.pipe(browserSync.stream());
});

// Task 4 - Compress images -> jpg can't be compressed, selecting png and gif only
gulp.task('images', function () {
	return gulp.src( paths.images.src, {base: './src'} )
		.pipe(changed( paths.images.dist ))
		.pipe(imagemin())
		.pipe(gulp.dest( paths.images.dist ))
		.pipe(browserSync.stream());
});

// Task 5 - Transfer untouched files -> jpg can't be compressed, transfered here
gulp.task('files_copy', function () {
	return gulp.src( paths.files.src, {base: './src'} )
		.pipe(changed( paths.files.dist ))
		.pipe(gulp.dest( paths.files.dist ))
		.pipe(browserSync.stream());
});

// Task 6 - Watch files
gulp.task('watch', gulp.parallel( 'browserWatch', function(done){
	gulp.watch( paths.stylesheets.src, gulp.series('stylesheets') );
	gulp.watch( paths.javascripts.src,  gulp.series('javascripts') );
	gulp.watch( paths.images.src,  gulp.series('images') );
	gulp.watch( paths.files.src , gulp.series('files_copy') );
	done();
}));

// Task 7 - Build all files
gulp.task('build', gulp.parallel('javascripts', 'stylesheets', 'images', 'files_copy' ) );

// Task 8 - Default
gulp.task('default', gulp.series('build', 'watch' ) );


var gulp           = require('gulp'),
		gutil          = require('gulp-util' ),
		sass           = require('gulp-sass'),
		concat         = require('gulp-concat'),
		uglify         = require('gulp-uglify'),
		cleanCSS       = require('gulp-clean-css'),
		rename         = require('gulp-rename'),
		imagemin       = require('gulp-imagemin'),
		cache          = require('gulp-cache'),
		autoprefixer   = require('gulp-autoprefixer'),
		notify         = require("gulp-notify");

gulp.task('common-js', function() {
	return gulp.src([
		'public/js/common.js'
		])
	.pipe(concat('common.min.js'))
	// .pipe(uglify())
	.pipe(gulp.dest('public/js'));
});

gulp.task('js', ['common-js'], function() {
	return gulp.src([
		'public/libs/jquery/dist/jquery.min.js',
		'public/libs/popper/popper.js',
		'public/libs/bootstrap/bootstrap.min.js',
		'public/libs/material-kit/material.min.js',
		// 'public/libs/material-kit/moment.min.js',
		// 'public/libs/material-kit/nouislider.min.js',
		// 'public/libs/material-kit/bootstrap-datetimepicker.js',
		'public/libs/material-kit/bootstrap-selectpicker.js',
		'public/libs/material-kit/bootstrap-tagsinput.js',
		'public/libs/jasny-bootstrap/jasny-bootstrap.js',
		'public/libs/material-kit/material-kit.js',
        'public/libs/dropzone/dropzone.js',
        'public/libs/nicescroll/jquery.nicescroll.min.js',
        'public/libs/moments/moment-with-locales.min.js',
        'public/libs/bootstrap-notify/bootstrap-notify.js',
        'public/libs/lightgallery/dist/js/lightgallery.js',
        'public/js/common.min.js' // Всегда в конце
		])
	.pipe(concat('scripts.min.js'))
	// .pipe(uglify())
	.pipe(gulp.dest('public/js'));
});

gulp.task('sass', function() {
	return gulp.src('public/sass/**/*.sass')
	.pipe(sass({outputStyle: 'expand'}).on("error", notify.onError()))
	.pipe(rename({suffix: '.min', prefix : ''}))
	.pipe(autoprefixer(['last 15 versions']))
	// .pipe(cleanCSS())
	.pipe(gulp.dest('public/css'));
});

gulp.task('watch', ['sass', 'js'], function() {
	gulp.watch('public/sass/**/*.sass', ['sass']);
	gulp.watch(['libs/**/*.js', 'public/js/common.js'], ['js']);
});

gulp.task('imagemin', function() {
	return gulp.src('public/img/**/*')
	.pipe(cache(imagemin()))
	.pipe(gulp.dest('public/img'));
});

gulp.task('build', ['imagemin', 'sass', 'js'], function() {

	var buildFiles = gulp.src([
		'public/*.html',
		'public/.htaccess',
		]).pipe(gulp.dest('public'));

	var buildCss = gulp.src([
		'public/css/main.min.css',
		]).pipe(gulp.dest('public/css'));

	var buildJs = gulp.src([
		'public/js/scripts.min.js',
		]).pipe(gulp.dest('public/js'));

	var buildFonts = gulp.src([
		'public/fonts/**/*',
		]).pipe(gulp.dest('public/fonts'));

});

gulp.task('clearcache', function () { return cache.clearAll(); });
gulp.task('default', ['watch']);
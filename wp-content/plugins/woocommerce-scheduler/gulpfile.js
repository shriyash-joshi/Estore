var gulp = require('gulp');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var minify = require('gulp-minify-css');
var changed = require('gulp-changed');

gulp.task('sass', function(){
  return gulp.src('assets/scss/**/*.scss')
    .pipe(concat('wdm-scheduler-style.css'))
    .pipe(sass())
    .pipe(minify())
    .pipe(gulp.dest('css'))
});

gulp.task('js', function(){
   return gulp.src('assets/js/**/*.js')
   .pipe(concat('wdm-scheduler-scripts.js'))
   .pipe(uglify())
   .pipe(gulp.dest('js'))
});

gulp.task("watch", function(){
  gulp.watch('assets/scss/**/*.scss', gulp.series('sass'));
  gulp.watch('assets/js/**/*.js', gulp.series('js'));
  
});
gulp.task('default', gulp.parallel('sass', 'js', 'watch'));
const gulp = require('gulp');
const nunjucks = require('gulp-nunjucks');
const beautify = require('gulp-beautify');
const sass = require('gulp-sass')(require('sass'));
const sync = require("browser-sync").create();
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const svgSymbols = require('gulp-svg-symbols');
const rename = require("gulp-rename");
const sourcemaps = require('gulp-sourcemaps');
const replace = require('gulp-replace');

function sprites() {
  return gulp.src('dist/img/icons/*.svg')
  .pipe(svgSymbols({
    templates: [`default-svg`]
  }))
  .pipe(rename('sprite.svg'))
  .pipe(gulp.dest('dist/img'))
  .pipe(sync.stream());
};

function njk() {
  let cbString = new Date().getTime();
  return gulp.src('src/*.njk')
    .pipe(nunjucks.compile({
      autoescape: false
    }))
    .pipe(
      replace(/cache_bust=\d+/g, () => "v=" + cbString)
    )
    .pipe(beautify.html({
      indent_size: 2,
      indent_with_tabs: true,
      indent_inner_html: true
    }))
    .pipe(gulp.dest('dist'))
    .pipe(sync.stream());
};

function scss() {
  const plugins = [
    autoprefixer(),
  ];
  return gulp.src('src/scss/styles.scss')
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(postcss(plugins))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('dist/css'))
    .pipe(sync.stream());
};

function browserSync() {
  gulp.series(njk, scss, sprites);
  sync.init({
      server: {
          baseDir: "./dist",
      },
      notify: false,
      online: true
  });
  gulp.watch('./dist/img/icons/*.svg', sprites);
  gulp.watch('./src/**/*.njk', njk);
  gulp.watch('./src/scss/**/*.scss', scss);

  gulp.watch('./dist/**.html').on('change', sync.reload);
  gulp.watch('./dist/js/**.js').on('change', sync.reload);
}

exports.default = gulp.series(njk, scss, sprites);
exports.sync = gulp.series(njk, scss, sprites, browserSync);

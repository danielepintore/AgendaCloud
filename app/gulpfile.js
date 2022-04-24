import gulp from 'gulp';
import newer from 'gulp-newer';
import size from 'gulp-size';
import imagemin from 'gulp-imagemin';
import cssnano from 'cssnano';
import rename from 'gulp-rename';
import postCss from 'gulp-postcss';
import htmlmin from 'gulp-htmlmin';
import jsobfuscator from 'gulp-javascript-obfuscator';
import autoprefixer from 'autoprefixer';
import browsersync from 'browser-sync';
import {merge} from "browser-sync/dist/cli/cli-options.js";

const paths = {
    styles: {
        src: 'public/css/**/*.css',
        dest: 'build/public/css/',
        excludeMin: '!public/css/**/*.min.css'
    },
    images: {
        src: 'public/img/**/*',
        dest: 'build/public/img/',
    },
    scripts: {
        src: 'public/js/**/*.js',
        dest: 'build/public/js/',
        excludeMin: '!public/js/**/*.min.js'
    },
    php: {
        src: 'src/**/*.php',
        dest: 'build/src/'
    },
    composer: {
        src: 'vendor/**/*',
        dest: 'build/vendor'
    }
};
/*
Css done
img done
js done
missing in public: admin api payment webfonts error.php index.php
missing resources
missing config
 */

/*
 * You can also declare named functions and export them as tasks
 */
// Bring third party dependencies from node_modules into vendor directory
function modules(done) {
    // Bootstrap JS
    var bootstrapJS = gulp.src('./node_modules/bootstrap/dist/js/bootstrap.bundle.min.js')
        .pipe(gulp.dest(paths.scripts.dest));
    // Bootstrap SCSS
    var bootstrapCSS = gulp.src('./node_modules/bootstrap/dist/css/bootstrap.min.css')
        .pipe(gulp.dest(paths.styles.dest));
    // Font Awesome
    var fontAwesomeCSS = gulp.src('./node_modules/@fortawesome/fontawesome-free/css/fontawesome.min.css')
        .pipe(gulp.dest(paths.styles.dest));
    var fontAwesomeFonts = gulp.src('./node_modules/@fortawesome/fontawesome-free/webfonts/*')
        .pipe(gulp.dest('build/public/webfonts'));
    // jQuery
    var jquery = gulp.src('./node_modules/jquery/dist/jquery.min.js')
        .pipe(gulp.dest(paths.scripts.dest));
    //@loadingio/loading.css
    var loadingio = gulp.src('./node_modules/@loadingio/loading.css/loading.min.css')
        .pipe(gulp.dest(paths.styles.dest));
    //JQuery validator
    var jqueryValidate = gulp.src('./node_modules/jquery-validation/dist/*.min.js')
        .pipe(gulp.dest(paths.scripts.dest));
    //Composer files
    var composerFile = gulp.src('composer.json')
        .pipe(gulp.dest('build/'));
    return merge(bootstrapJS, bootstrapCSS, fontAwesomeCSS, fontAwesomeFonts, jqueryValidate, jquery, loadingio, composerFile);
}

export function minifyImages() {
    return gulp.src(paths.images.src)
        .pipe(newer(paths.images.dest))
        .pipe(imagemin({minOpts: {optimizationLevel: 5}}))
        .pipe(size({showFiles:true}))
        // pass in options to the stream
        .pipe(gulp.dest(paths.images.dest));
}

export function generateStyles() {
    return gulp.src([paths.styles.src, paths.styles.excludeMin])
        .pipe(postCss([autoprefixer(), cssnano()]))
        .pipe(gulp.dest(paths.styles.dest));
}

export function minifyScripts() {
    return gulp.src([paths.scripts.src, paths.scripts.excludeMin])
        .pipe(jsobfuscator({
            compact: true,
            controlFlowFlattening: true,
            controlFlowFlatteningThreshold: 0.75,
            deadCodeInjection: true,
            deadCodeInjectionThreshold: 0.4,
            debugProtection: false,
            debugProtectionInterval: 0,
            disableConsoleOutput: true,
            identifierNamesGenerator: 'hexadecimal',
            log: false,
            numbersToExpressions: true,
            renameGlobals: false,
            selfDefending: true,
            simplify: true,
            splitStrings: true,
            splitStringsChunkLength: 10,
            stringArray: true,
            stringArrayCallsTransform: true,
            stringArrayCallsTransformThreshold: 0.75,
            stringArrayEncoding: ['base64'],
            stringArrayIndexShift: true,
            stringArrayRotate: true,
            stringArrayShuffle: true,
            stringArrayWrappersCount: 2,
            stringArrayWrappersChainedCalls: true,
            stringArrayWrappersParametersMaxCount: 4,
            stringArrayWrappersType: 'function',
            stringArrayThreshold: 0.75,
            transformObjectKeys: true,
            unicodeEscapeSequence: false
        }))
        .pipe(gulp.dest(paths.scripts.dest));
}

function copyPhpSources(done) {
     var srcDir = gulp.src(paths.php.src)
         .pipe(htmlmin({
             collapseWhitespace: true,
             ignoreCustomFragments: [/<\?[\s\S]*?(?:\?>|$)/]
            }))
         .pipe(gulp.dest(paths.php.dest));

     var publicDir = gulp.src('public/**/*.php')
         .pipe(htmlmin({
             collapseWhitespace: true,
             ignoreCustomFragments: [/<\?[\s\S]*?(?:\?>|$)/]
         }))
         .pipe(gulp.dest('build/public/'));

     var resourcesDir = gulp.src('resources/**/*.php')
         .pipe(gulp.dest('build/resources/'));

    var configDir = gulp.src('config/**/*.php')
        .pipe(gulp.dest('build/config/'));

     done();
    return merge(srcDir, publicDir, resourcesDir, configDir);
}

// browser-sync
export function server(done) {
    if (browsersync) browsersync.init({
        proxy: "agendacloud.it:3000"});
    done();
}


/**************** watch task ****************/
function watch(done) {

    // image changes
    gulp.watch(paths.images.src).on('change', browsersync.reload);

    // JS changes
    gulp.watch(paths.scripts.src).on('change', browsersync.reload);

    // CSS changes
    gulp.watch([paths.styles.src]).on('change', browsersync.reload);


    // PHP changes
    gulp.watch([paths.php.src, 'public/**/*.php', 'resources/**/*.php', 'config/**/*.php']).on('change', browsersync.reload);

    // ENV file change
    gulp.watch('baseEnv').on('change', browsersync.reload);

    // Composer file change
    gulp.watch('composer.json').on('change', browsersync.reload);
    gulp.watch('vendor/**/*').on('change', browsersync.reload);

    done();

}

const build = gulp.series(modules, copyPhpSources, minifyImages, generateStyles, minifyScripts);
const run = gulp.series(watch, server);
export default run;
export {build as build};
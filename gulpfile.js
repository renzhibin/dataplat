//导入工具包 require('node_modules里对应模块')
var gulp = require('gulp'), //本地安装gulp所用到的地方
    less = require('gulp-less'),
    rev = require('gulp-rev'),
    nodepath = require('path'),
    through = require('through2'),
    gutil = require('gulp-util'),
    inject = require('gulp-inject'),
    cleanCSS = require('gulp-clean-css'),
    revReplace = require("gulp-rev-replace");

var path = {
        app: process.cwd(), //当前app的目录路径
        basePath: './datawork/websites/visual', //基础目录
        lessPath: './datawork/websites/visual/assets/css/theme/', //需打包编译的less路径和产出css文件的目录

    }
    //定义一个testLess任务（自定义任务名称）
gulp.task('testLess', function() {
    gulp.src(path.lessPath + 'd_theme_main.less') //该任务针对的文件
        .pipe(less()) //该任务调用的模块
        .pipe(cleanCSS())
        .pipe(gulp.dest(path.lessPath)); //将会在src/css下生成index.css
});

gulp.task('rev', ['testLess'], function() {
    return gulp.src([path.lessPath + 'd_theme_main.css'])
        .pipe(rev())
        .pipe(function() {
            var hashes = {},
                oPath = '';
            var collect = function(file, enc, cb) {
                if (file.revHash) {
                    var filename = nodepath.basename(file.revOrigPath);
                    hashes[filename] = filename + '?v=' + file.revHash;
                    oPath = file.base;
                }
                return cb();
            }

            var emit = function(cb) {
                if (oPath) {
                    var file = new gutil.File({
                        base: oPath,
                        cwd: process.cwd(),
                        path: nodepath.resolve(oPath, 'rev-manifest.json')
                    });
                    file.contents = new Buffer(JSON.stringify(hashes, null, 4));
                    this.push(file);
                }
                return cb();
            }
            return through.obj(collect, emit);
        }())
        .pipe(gulp.dest(path.app))
        .pipe(rev.manifest())
})

gulp.task('revCollector', ['rev'], function() {
    var manifest = gulp.src([nodepath.resolve(path.app, 'rev-manifest.json')]);
    var cssPath = nodepath.resolve(path.lessPath, 'd_theme_main.css');

    return gulp.src([path.basePath + '/protected/views/layouts/lib.tpl'])
        .pipe(inject(gulp.src(cssPath, {read: false}), {
            transform: function (filepath, file, i, length) {
                var fpath = ['/assets', filepath.split('assets')[1]].join('');
                return '<link rel="stylesheet" href="'+fpath+'">';
            }
        }))
        .pipe(revReplace({ 
            manifest: manifest,
            replaceInExtensions: ['.tpl']
        }))
        .pipe(gulp.dest(path.basePath + '/protected/views/layouts/'));
})

gulp.task('watch', function() {
    gulp.watch(path.lessPath + '*.less', ['revCollector']);
})


gulp.task('default', ['revCollector', 'watch']); //定义默认任务


//gulp.task(name[, deps], fn) 定义任务  name：任务名称 deps：依赖任务名称 fn：回调函数
//gulp.src(globs[, options]) 执行任务处理的文件  globs：处理的文件路径(字符串或者字符串数组) 
//gulp.dest(path[, options]) 处理完后文件生成路径

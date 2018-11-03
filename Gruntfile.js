module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: [
            'htdocs/assets',
            'cache/assets_versioning.json'
        ],
        source: {
            source_node: 'node_modules',
            source_js: 'htdocs/script',
            source_tinymce: '<%= sources.js %>/tinymce-4.4.0'
        },
        assets: {
            assets_main: 'htdocs/assets',
            assets_module: 'module/*/assets',
            js: '<%= assets_main %>/js',
            module_js: '<%= assets_module %>/js',
            img: '<%= dir.assets_main %>/img',
            module_img: '<%= dir.assets_module %>/img',
            fonts: '<%= assets_main %>/fonts'
        },
        copy: {
            images: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: [
                            'module/*/assets/img/*'
                        ],
                        dest: 'htdocs/assets/img/'
                    }
                ]
            },
            fontawesome: {
                files: [
                    {
                        expand: true,
                        src: [
                            '**/*'
                        ],
                        dest: 'web/fonts/',
                        cwd: 'node_modules/font-awesome/fonts/',
                        filter: 'isFile'
                    }
                ]
            },
            lato: {
                files: [
                    {
                        expand: true,
                        src: [
                            '**/*'
                        ],
                        dest: 'web/fonts/',
                        cwd: 'node_modules/lato-font/fonts/',
                        filter: 'isFile'
                    }
                ]
            },
            jqueryui_images: {
                files: [
                    {
                        expand: true,
                        src: '*',
                        cwd: 'node_modules/jquery-ui/themes/base/images/',
                        dest: 'web/css/images/'
                    }
                ]
            },
            js: {
                files: [
                    {
                        expand: true,
                        src: [
                            '**/*.js',
                            '**/*.css'
                        ],
                        cwd: 'node_modules/tinymce',
                        dest: 'htdocs/assets/js/tinymce'
                    },
                    {
                        expand: true,
                        src: ['module/**/*.js', '!module/Core/assets/js/*.js'],
                        dest: 'htdocs/assets/js',
                        rename: function (dest, src) {          // The `dest` and `src` values can be passed into the function
                            return dest + '/' + src.replace('module/', '').replace('/assets/js', '').toLowerCase(); // The `src` is being renamed; the `dest` remains the same
                        }
                    },
                    {
                        expand: true,
                        src: ['node_modules/jquery-ui/ui/widgets/autocomplete.js'],
                        dest: 'htdocs/assets/js'
                    }
                ]
            }
        },
        sass: {
            compileBeWelcome: {
                options: {
                    style: 'expanded'
                },
                files: {
                    'web/css/app.css': 'src/App/Resources/scss/bewelcome.scss'
                }
            }
        },
        csslint: {
            options: {
                csslintrc: '.csslintrc'
            },
            bewelcome: {
                expand: true,
                src: 'module/*/assets/css/*.css'
            }
        },
        jshint: {
            all: [
                'Gruntfile.js',
                'module/*/assets/js/*.js'
            ],
            options: {
                jshintrc: '.jshintrc'
            }
        }
/*
        css: {
            src: [
                'node_modules/font-awesome/css/font-awesome.css',
                'node_modules/lato-font/css/lato-font.css',
                'htdocs/assets/sass/*.css'
            ],
            dest: 'htdocs/assets/css/styles.css',
            nonull: true
        },
        jqueryui: {
            src: [
                'node_modules/jquery-ui/themes/base/core.css',
                'node_modules/jquery-ui/themes/base/autocomplete.css',
                'node_modules/jquery-ui/themes/base/menu.css',
                'node_modules/jquery-ui/themes/base/theme.css'
            ],
            dest: 'htdocs/assets/css/jquery-ui.css',
            nonull: true
        }
*/
    });


    // Load the plugin that provides the ('grunt-*') task.
    grunt.loadNpmTasks('grunt-assets-versioning');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-csslint');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Default task for development
    grunt.registerTask('default', ['sass']);

    // Task for production - adds asset versioning
    grunt.registerTask('build-production', ['build', 'assets_versioning']);

    // Aggregate tasks
//    grunt.registerTask('check', ['checkcss', 'checkjs']);
//    grunt.registerTask('build', ['clean', 'copy', 'buildcss', 'buildjs']);

    // CSS
//    grunt.registerTask('checkcss', ['csslint']);
//    grunt.registerTask('buildcss', ['sass', 'concat:css', 'concat:jqueryui', 'cssmin']);

    // JS
    grunt.registerTask('checkjs', ['jshint']);
//    grunt.registerTask('buildjs', ['concat:dist', 'concat:backwards', 'concat:leaflet', 'concat:jqueryuijs',
//        , 'uglify'
//    ]);

};

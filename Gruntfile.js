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
                        dest: 'htdocs/assets/fonts/',
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
                        dest: 'htdocs/assets/fonts/',
                        cwd: 'node_modules/lato-font/fonts/',
                        filter: 'isFile'
                    }
                ]
            },
            signika: {
                files: [
                    {
                        expand: true,
                        src: '**/*',
                        dest: 'htdocs/assets/fonts/',
                        cwd: 'module/Core/assets/fonts',
                        filter: 'isFile'
                    }
                ]
            },
            leaflet_css: {
                files: [
                    {
                        expand: true,
                        src: 'leaflet.css',
                        cwd: 'node_modules/leaflet/dist',
                        dest: 'htdocs/assets/css/'
                    }
                ]
            },
            jqueryui_images: {
                files: [
                    {
                        expand: true,
                        src: '*',
                        cwd: 'node_modules/jquery-ui/themes/base/images/',
                        dest: 'htdocs/assets/css/images/'
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
                        dest: 'htdocs/assets/js'
                    },
                    {
                        expand: true,
                        src: 'communitynews.js',
                        cwd: 'module/Admin/assets/js',
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
                    'htdocs/assets/sass/bewelcome.css': 'htdocs/styles/scss/bewelcome.scss'
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
        cssmin: {
            css: {
                files: [{
                    expand: true,
                    src: '**/*.css',
                    dest: 'htdocs/assets/css/',
                    cwd: 'htdocs/assets/css'
                }]
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
        },
        concat: {
            dist: {
                src: [
                    'node_modules/jquery/dist/jquery.js',
                    'node_modules/jquery-ui/ui/core.js',
                    'node_modules/jquery-ui/ui/widget.js',
                    'node_modules/jquery-ui/ui/position.js',
                    'node_modules/jquery-ui/ui/menu.js',
                    'node_modules/tether/dist/js/tether.js',
                    'node_modules/bootstrap/dist/js/bootstrap.js',
                    'node_modules/bootstrap-autohidingnavbar/dist/jquery.bootstrap-autohidingnavbar.js',
                    'node_modules/select2/dist/js/select2.js',

                    // Auto complete used by map search for drop down search box
                    'node_modules/jquery-ui/ui/autocomplete.js',

                    'node_modules/skrollr/dist/skrollr.min.js',
                    'node_modules/skrollr-menu/dist/skrollr.menu.min.js',

                    'node_modules/unslider/src/js/unslider.js',
                    'node_modules/tinymce/tinymce.js',
                    'module/*/assets/js/**/*.js'
                ],
                dest: 'htdocs/assets/js/built.js',
                nonull: true
            },
            backwards: {
                src: [
                    'node_modules/html5shiv/dist/html5shiv.js',
                    'node_modules/respond.js/dest/respond.min.js',
                ],
                dest: 'htdocs/assets/js/backwards.js',
                nonull: true
            },
            // Leaflet is about 150KB minified, so it is a separate file.
            leaflet: {
                src: [
                    'node_modules/leaflet/dist/leaflet-src.js',
                    'node_modules/leaflet.markercluster/dist/leaflet.markercluster-src.js',
                ],
                dest: 'htdocs/assets/js/leaflet.js',
                nonull: true
            },
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
                    'node_modules/jquery-ui/themes/base/theme.css',
                    'node_modules/jquery-ui/themes/base/autocomplete.css',
                    'node_modules/jquery-ui/themes/base/menu.css'
                ],
                dest: 'htdocs/assets/css/jquery-ui.css',
                nonull: true
            }
        },
        uglify: {
            options: {
                sourceMap: true
            },
            js: {
                files: [{
                    expand: true,
                    src: '**/*.js',
                    dest: 'htdocs/assets/js',
                    cwd: 'htdocs/assets/js'
                }]
            }
        },
        assets_versioning: {
            options: {
                versionsMapFile: 'cache/assets_versioning.json',
                versionsMapTrimPath: 'htdocs/'
            },
            assets: {
                options: {
                    tasks: [
                        'uglify:js',
                        'cssmin:css'
                    ]
                }
            }
        },
        watch: {
            sass: {
                files: 'htdocs/styles/scss/*',
                tasks: ['sass:compileBeWelcome', 'concat:css']
            },
            css: {
                files: 'module/*/assets/css/**/*.css',
                tasks: ['csslint:bewelcome', 'concat:css']
            },
            js: {
                files: ['module/*/assets/js/**/*.js'],
                tasks: ['jshint', 'concat:dist'],
                options: {
                    reload: true
                }
            }
        }
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
    grunt.loadNpmTasks('grunt-recess');

    // Default task for development
    grunt.registerTask('default', ['check', 'build']);

    // Task for production - adds asset versioning
    grunt.registerTask('build-production', ['build', 'assets_versioning']);

    // Aggregate tasks
    grunt.registerTask('check', ['checkcss', 'checkjs']);
    grunt.registerTask('build', ['clean', 'copy', 'buildcss', 'buildjs']);

    // CSS
    grunt.registerTask('checkcss', ['csslint']);
    grunt.registerTask('buildcss', ['sass', 'concat:css', 'concat:jqueryui', 'cssmin']);

    // JS
    grunt.registerTask('checkjs', ['jshint']);
    grunt.registerTask('buildjs', ['concat:dist', 'concat:backwards', 'concat:leaflet', 'uglify']);

};

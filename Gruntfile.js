module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        clean: [
            'htdocs/assets',
            'cache/assets_versioning.json'
        ],
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
        csscomb: {
            bewelcome: {
                expand: true,
                src: 'module/*/assets/css/*.css'
            }
        },
        concat_css: {
            bw: {
                src: [
                    'node_modules/font-awesome/css/font-awesome.css',
                    'node_modules/lato-font/css/lato-font.css',
                    'htdocs/assets/sass/*.css'
                ],
                dest: 'htdocs/assets/css/styles.css',
                nonull: true
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
                    'node_modules/tether/dist/js/tether.js',
                    'node_modules/bootstrap/dist/js/bootstrap.js',
                    'node_modules/bootstrap-autohidingnavbar/dist/jquery.bootstrap-autohidingnavbar.js',
                    'node_modules/select2/dist/js/select2.js',

                    'node_modules/jquery-ui/ui/core.js',
                    'node_modules/jquery-ui/ui/widget.js',
                    'node_modules/jquery-ui/ui/position.js',
                    'node_modules/jquery-ui/ui/menu.js',
                    // Auto complete used by map search for drop down search box
                    'node_modules/jquery-ui/ui/autocomplete.js',

                    'node_modules/skrollr/dist/skrollr.min.js',
                    'node_modules/skrollr-menu/dist/skrollr.menu.min.js',

                    'node_modules/unslider/src/js/unslider.js',

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
                tasks: ['sass:compileBeWelcome', 'concat_css:bw']
            },
            css: {
                files: 'module/*/assets/css/**/*.css',
                tasks: ['csscomb:bewelcome', 'csslint:bewelcome', 'concat_css:bw']
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
    grunt.loadNpmTasks('grunt-concat-css');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-csslint');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-csscomb');
    grunt.loadNpmTasks('grunt-recess');

    // Default task for development
    grunt.registerTask('default', ['check', 'build']);

    // Task for production - adds asset versioning
    grunt.registerTask('build-production', ['build', 'assets_versioning']);

    // Aggregate tasks
    grunt.registerTask('check', ['checkcss', 'checkjs']);
    grunt.registerTask('build', ['clean', 'copy', 'buildcss', 'buildjs']);

    // CSS
    grunt.registerTask('checkcss', ['csslint', 'csscomb']);
    grunt.registerTask('buildcss', ['sass', 'concat_css', 'cssmin']);

    // JS
    grunt.registerTask('checkjs', ['jshint']);
    grunt.registerTask('buildjs', ['concat', 'uglify']);

};

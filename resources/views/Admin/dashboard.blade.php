@extends('Admin.layouts.app')

@section('title')
    {{ 'Dashboard' }}
@endsection

@section('content')
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <!-- Community Stats Swiper -->
            <div class="col-lg-6 mb-4">
                <!-- Slide 1: User Overview -->

                <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg"
                    id="swiper-with-pagination-cards">
                    <div class="swiper-wrapper">
                        <!-- Slide 1: User Overview -->
                        <div class="swiper-slide">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="text-white mb-0 mt-2">Community Overview</h5>
                                    <small>Total Users: {{ $totalUsers }}</small>
                                </div>
                                <div class="row">
                                    <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1">
                                        <h6 class="text-white mt-0 mt-md-3 mb-3">User Roles</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $studentCount }}</p>
                                                        <p class="mb-0">Students</p>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-2">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $academicCount }}</p>
                                                        <p class="mb-0">Academics</p>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex mb-4 align-items-center">
                                                        <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                            {{ $totalUsers }}</p>
                                                        <p class="mb-0">Total Users</p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                                        <img src="{{ asset('assets/img/illustrations/card-website-analytics-1.png') }}"
                                            alt="User Stats" width="170" class="card-website-analytics-img" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Slide 2: Gender Breakdown -->
                        <div class="swiper-slide">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="text-white mb-0 mt-2">Gender Demographics</h5>
                                    <small>Based on user profiles</small>
                                </div>
                                <div class="col-lg-7 col-md-9 col-12 order-2 order-md-1">
                                    <h6 class="text-white mt-0 mt-md-3 mb-3">User Count</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex mb-4 align-items-center">
                                                    <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                        {{ $maleCount }}</p>
                                                    <p class="mb-0">Males</p>
                                                </li>
                                                <li class="d-flex align-items-center mb-2">
                                                    <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                        {{ $femaleCount }}</p>
                                                    <p class="mb-0">Females</p>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-6">
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex mb-4 align-items-center">
                                                    <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                        {{ $malePercentage }}%</p>
                                                    <p class="mb-0">Male Ratio</p>
                                                </li>
                                                <li class="d-flex align-items-center mb-2">
                                                    <p class="mb-0 fw-medium me-2 website-analytics-text-bg">
                                                        {{ $femalePercentage }}%</p>
                                                    <p class="mb-0">Female Ratio</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-md-3 col-12 order-1 order-md-2 my-4 my-md-0 text-center">
                                    <img src="{{ asset('assets/img/illustrations/card-website-analytics-2.png') }}"
                                        alt="Gender Stats" width="170" class="card-website-analytics-img" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
            <!--/ Community Stats Swiper -->

            <!-- Total Posts -->
            <div class="col-lg-6 mb-4">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card h-100">
                            <div class="card-body pb-0">
                                <div class="card-icon">
                                    <span class="badge bg-label-info rounded-pill p-2">
                                        <i class="ti ti-message-dots ti-sm"></i>
                                    </span>
                                </div>
                                <h5 class="card-title mb-0 mt-2">{{ $postCount }}</h5>
                                <small>Total Posts</small>
                            </div>
                            <div id="postsChart"></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card h-100">
                            <div class="card-body pb-0">
                                <div class="card-icon">
                                    <span class="badge bg-label-success rounded-pill p-2">
                                        <i class="ti ti-message-circle ti-sm"></i>
                                    </span>
                                </div>
                                <h5 class="card-title mb-0 mt-2">{{ $commentCount }}</h5>
                                <small>Total Comments</small>
                            </div>
                            <div id="commentsChart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Total Comments -->

            <!-- Earning Reports -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-0">Recent Platform Activity</h5>
                            <small class="text-muted">New Posts in the Last 7 Days</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-12 col-md-4 d-flex flex-column align-self-end">
                                <div class="d-flex gap-2 align-items-center mb-2 pb-1 flex-wrap">
                                    <h1 class="mb-0">{{ $postCount }}</h1>
                                    <div class="badge rounded bg-label-success">Total</div>
                                </div>
                                <small>Total number of posts created on the platform</small>
                            </div>
                            <div class="col-12 col-md-8">
                                <div id="weeklyPostsChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ Earning Reports -->

            <!-- Support Tracker -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between pb-0">
                        <div class="card-title mb-0">
                            <h5 class="mb-0">Top Likers</h5>
                            <small class="text-muted">Users with the most likes on their posts</small>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($topLikers->isNotEmpty())
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-12 col-lg-4">
                                    <div class="mt-lg-4 mt-lg-2 mb-lg-4 mb-2 pt-1">
                                        <h1 class="mb-0">{{ $totalTopLikes }}</h1>
                                        <p class="mb-0">Likes for Top 3</p>
                                    </div>
                                    <ul class="p-0 m-0">
                                        @if (isset($topLikers[0]))
                                            <li class="d-flex gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                                                <div class="avatar">
                                                    <img src="{{ $topLikers[0]->profile_picture_url ?? asset('assets/img/avatars/1.png') }}"
                                                        alt="User" class="rounded-circle">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-nowrap">{{ $topLikers[0]->first_name }}</h6>
                                                    <small class="text-muted">{{ $topLikers[0]->total_likes_count }}
                                                        Likes</small>
                                                </div>
                                            </li>
                                        @endif
                                        @if (isset($topLikers[1]))
                                            <li class="d-flex gap-3 align-items-center mb-lg-3 pb-1">
                                                <div class="avatar">
                                                    <img src="{{ $topLikers[1]->profile_picture_url ?? asset('assets/img/avatars/1.png') }}"
                                                        alt="User" class="rounded-circle">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-nowrap">{{ $topLikers[1]->first_name }}</h6>
                                                    <small class="text-muted">{{ $topLikers[1]->total_likes_count }}
                                                        Likes</small>
                                                </div>
                                            </li>
                                        @endif
                                        @if (isset($topLikers[2]))
                                            <li class="d-flex gap-3 align-items-center pb-1">
                                                <div class="avatar">
                                                    <img src="{{ $topLikers[2]->profile_picture_url ?? asset('assets/img/avatars/1.png') }}"
                                                        alt="User" class="rounded-circle">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-nowrap">{{ $topLikers[2]->first_name }}</h6>
                                                    <small class="text-muted">{{ $topLikers[2]->total_likes_count }}
                                                        Likes</small>
                                                </div>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="col-12 col-sm-8 col-md-12 col-lg-8">
                                    <div id="topLikersChart"></div>
                                </div>
                            </div>
                        @else
                            <div class="text-center my-4">
                                <p>No users with likes yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!--/ User Growth -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">User Growth</h5>
                        <small class="text-muted">New Registrations in the Last 4 Weeks</small>
                    </div>
                    <div class="card-body">
                        <div id="userGrowthChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4 d-flex flex-column">
                <div class="row h-100">
                    <div class="col-sm-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="badge rounded bg-label-warning p-2 mb-2">
                                    <i class="ti ti-user-check ti-lg"></i>
                                </div>
                                <h5 class="card-title mb-1">{{ $dailyActiveUsers }}</h5>
                                <p class="mb-0">Daily Active Users</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="badge rounded bg-label-warning p-2 mb-2">
                                    <i class="ti ti-calendar-stats ti-lg"></i>
                                </div>
                                <h5 class="card-title mb-1">{{ $monthlyActiveUsers }}</h5>
                                <p class="mb-0">Monthly Active Users</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="badge rounded bg-label-success p-2 mb-2">
                                    <i class="ti ti-book ti-lg"></i>
                                </div>
                                <h5 class="card-title mb-1">{{ $libraryFileCount }}</h5>
                                <p class="mb-0">Approved Library Files</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="badge rounded bg-label-primary p-2 mb-2">
                                    <i class="ti ti-user-plus ti-lg"></i>
                                </div>
                                <h5 class="card-title mb-1">{{ $newUsersCount }}</h5>
                                <p class="mb-0">New Users (Last 30d)</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title m-0 me-2">Top Content Creators</h5>
                        <small class="text-muted">Users with the most posts</small>
                    </div>
                    <div class="card-body">
                        @if ($topCreators->isNotEmpty())
                            <ul class="list-unstyled mb-0">
                                @foreach ($topCreators as $creator)
                                    <li class="d-flex align-items-center mb-3">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <img src="{{ $creator->profile_picture_url ?? asset('assets/img/avatars/1.png') }}"
                                                alt="User" class="rounded-circle">
                                        </div>
                                        <div
                                            class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <h6 class="mb-0">{{ $creator->first_name }}</h6>
                                                <small class="text-muted">{{ $creator->role->value }}</small>
                                            </div>
                                            <div class="user-progress">
                                                <p class="fw-medium mb-0">{{ $creator->posts_count }} Posts</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-center">No posts have been created yet.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title m-0 me-2">Library File Status</h5>
                    </div>
                    <div class="card-body">
                        <div id="libraryStatusChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="m-0 me-2">Recent Library Submissions</h5>
                            <small class="text-muted">Latest 5 file uploads</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="p-0 m-0">
                            @forelse ($recentSubmissions as $submission)
                                <li class="d-flex align-items-center mb-4">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <img src="{{ $submission->user->profile_picture_url ?? asset('assets/img/avatars/1.png') }}"
                                            alt="User" class="rounded-circle">
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0 text-truncate" style="max-width: 150px;">
                                                {{ $submission->title }}</h6>
                                            <small class="text-muted">by {{ $submission->user->first_name }}</small>
                                        </div>
                                        <div class="user-progress">
                                            @if ($submission->status == 'approved')
                                                <span class="badge bg-label-success">Approved</span>
                                            @elseif ($submission->status == 'pending')
                                                <span class="badge bg-label-warning">Pending</span>
                                            @else
                                                <span class="badge bg-label-danger">Rejected</span>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center">No submissions yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        @endsection
        @push('scripts')
            <script>
                @if (isset($postChartLabels) && isset($postChartData))
                    const weeklyPostsChartEl = document.querySelector('#weeklyPostsChart');
                    if (weeklyPostsChartEl) {
                        const weeklyPostsChartConfig = {
                            // ✨ --- START of corrected configuration --- ✨
                            chart: {
                                height: 200,
                                type: 'area', // Changed from 'bar' to 'area'
                                toolbar: {
                                    show: false
                                },
                                sparkline: {
                                    enabled: true
                                } // Hides axes for a cleaner look
                            },
                            series: [{
                                name: 'New Posts',
                                data: {!! $postChartData !!}
                            }],
                            xaxis: {
                                categories: {!! $postChartLabels !!},
                                labels: {
                                    show: false
                                },
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                }
                            },
                            yaxis: {
                                labels: {
                                    show: false
                                }
                            },
                            colors: [config.colors.primary],
                            grid: {
                                show: false,
                                padding: {
                                    top: 10,
                                    bottom: 0,
                                    left: -10,
                                    right: -10
                                }
                            },
                            stroke: {
                                width: 2,
                                curve: 'smooth' // Makes the line smooth
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 0.8,
                                    opacityFrom: 0.5,
                                    opacityTo: 0.1,
                                    stops: [0, 85, 100]
                                }
                            },
                            tooltip: {
                                enabled: true, // Shows details on hover
                                y: {
                                    formatter: function(val) {
                                        return val + " Posts";
                                    }
                                }
                            }
                            // ✨ --- END of corrected configuration --- ✨
                        };

                        const weeklyPostsChart = new ApexCharts(weeklyPostsChartEl, weeklyPostsChartConfig);
                        weeklyPostsChart.render();
                    }
                @endif
            </script>
        @endpush
        @push('scripts')
            <script>
                // Check if the data for the top likers chart exists
                @if (isset($topLikers) && $topLikers->isNotEmpty())
                    const topLikersChartEl = document.querySelector('#topLikersChart');
                    if (topLikersChartEl) {
                        const topLikersChartConfig = {
                            chart: {
                                height: 280,
                                type: 'radialBar'
                            },
                            series: [{{ $chartPercentage }}], // The percentage calculated in the controller
                            plotOptions: {
                                radialBar: {
                                    startAngle: -90,
                                    endAngle: 90,
                                    offsetY: 10,
                                    hollow: {
                                        size: '65%'
                                    },
                                    track: {
                                        background: '#E4E5E7', // Light grey background for the track
                                        strokeWidth: '100%'
                                    },
                                    dataLabels: {
                                        name: {
                                            show: true,
                                            fontSize: '15px',
                                            fontWeight: '500',
                                            offsetY: -5,
                                            color: '#5d596c',
                                            formatter: function(w) {
                                                return 'Top Liker Progress';
                                            }
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '32px',
                                            fontWeight: '600',
                                            offsetY: 5,
                                            color: '#5d596c',
                                            formatter: function(val) {
                                                return val + '%';
                                            }
                                        }
                                    }
                                }
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'dark',
                                    type: 'horizontal',
                                    shadeIntensity: 0.5,
                                    gradientToColors: [config.colors.primary],
                                    inverseColors: true,
                                    opacityFrom: 1,
                                    opacityTo: 1,
                                    stops: [0, 100]
                                }
                            },
                            stroke: {
                                lineCap: 'round'
                            },
                            grid: {
                                padding: {
                                    top: -10
                                }
                            }
                        };

                        const topLikersChart = new ApexCharts(topLikersChartEl, topLikersChartConfig);
                        topLikersChart.render();
                    }
                @endif
@if(isset($userGrowthLabels) && isset($userGrowthData))
            const userGrowthChartEl = document.querySelector('#userGrowthChart');
            if(userGrowthChartEl) {
                // Get the dynamic colors from your theme's config file
                const axisColor = config.colors.axisColor;
                const borderColor = config.colors.borderColor;

                const userGrowthChartConfig = {
                    chart: {
                        type: 'line',
                        height: 280,
                        toolbar: { show: false }
                    },
                    series: [{
                        name: 'New Users',
                        data: {!! $userGrowthData !!}
                    }],
                    xaxis: {
                        categories: {!! $userGrowthLabels !!},
                        // ✨ This makes the bottom labels (weeks) theme-aware
                        labels: {
                            style: {
                                colors: axisColor
                            }
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        // ✨ This makes the side labels (counts) theme-aware
                        labels: {
                            style: {
                                colors: axisColor
                            }
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    grid: {
                        show: true,
                        // ✨ This makes the grid lines theme-aware
                        borderColor: borderColor,
                        strokeDashArray: 5,
                    },
                    colors: [config.colors.success]
                };
                const userGrowthChart = new ApexCharts(userGrowthChartEl, userGrowthChartConfig);
                userGrowthChart.render();
            }
        @endif
                @if(isset($libraryChartLabels) && isset($libraryChartSeries))
            const libraryStatusChartEl = document.querySelector('#libraryStatusChart');
            if(libraryStatusChartEl) {
                // Get the theme's text color, which changes between light and dark modes
            const axisColor = config.colors.axisColor;
            // Get the theme's pure white color variable
            const whiteColor = config.colors.white;

                const libraryStatusChartConfig = {
                    chart: { type: 'donut', height: 280, toolbar: { show: false } },
                    series: {!! $libraryChartSeries !!},
                    labels: {!! $libraryChartLabels !!},
                    legend: {
                        show: true,
                        position: 'bottom',
                        // ✨ --- This is the corrected part --- ✨
                        labels: {
                            // We provide an array of colors.
                            // The order corresponds to your labels: [approved, pending, rejected]
                        colors: [whiteColor, whiteColor, axisColor]
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: (val) => val.toFixed(0) + '%'
                    },
                    colors: [config.colors.success, config.colors.warning, config.colors.danger],
                    stroke: { width: 5 }
                };
                const libraryStatusChart = new ApexCharts(libraryStatusChartEl, libraryStatusChartConfig);
            libraryStatusChart.render();
            }
        @endif
            </script>
        @endpush
        @push('scripts')

            <script>
                // Blue Chart for "Total Posts" Card
                const postsChartEl = document.querySelector('#postsChart');
                if (postsChartEl) {
                    const postsChartConfig = {
                        chart: {
                            type: 'area',
                            height: 40,
                            sparkline: {
                                enabled: true
                            }
                        },
                        series: [{
                            // This is sample data. It can be made dynamic later.
                            data: [10, 15, 12, 18, 15, 11, 16]
                        }],
                        colors: [config.colors.info], // Blue color
                        stroke: {
                            width: 2,
                            curve: 'smooth'
                        },
                        tooltip: {
                            enabled: false
                        }
                    };
                    const postsChart = new ApexCharts(postsChartEl, postsChartConfig);
                    postsChart.render();
                }

                // Green Chart for "Total Comments" Card
                const commentsChartEl = document.querySelector('#commentsChart');
                if (commentsChartEl) {
                    const commentsChartConfig = {
                        chart: {
                            type: 'area',
                            height: 40,
                            sparkline: {
                                enabled: true
                            }
                        },
                        series: [{
                            // This is sample data. It can be made dynamic later.
                            data: [8, 12, 9, 14, 11, 17, 10]
                        }],
                        colors: [config.colors.success], // Green color
                        stroke: {
                            width: 2,
                            curve: 'smooth'
                        },
                        tooltip: {
                            enabled: false
                        }
                    };
                    const commentsChart = new ApexCharts(commentsChartEl, commentsChartConfig);
                    commentsChart.render();
                }
            </script>
        @endpush

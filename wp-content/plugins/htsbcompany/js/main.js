jQuery(document).ready(function ($) {
    // 更新页面标题
    function updatePageTitle(category) {
        if (category === 'all') {
            $('.page-header h1').text('所有产品');
        } else {
            // 获取分类名称
            var activeLink = $('.category-filter a[data-category="' + category + '"]');
            if (activeLink.length) {
                var categoryName = activeLink.contents().filter(function () {
                    return this.nodeType === 3; // 文本节点
                }).text().trim();

                // 移除数量信息
                categoryName = categoryName.replace(/\(\d+\)/, '').trim();
                $('.page-header h1').text(categoryName);
            }
        }
    }
    // 移动菜单切换
    $('.mobile-menu-toggle').click(function () {
        $('.mobile-menu-container').slideToggle();
    });

    // 表单验证
    $('#contactForm').submit(function (e) {
        var valid = true;

        // 清除之前的错误消息
        $('.error-message').remove();

        // 验证必填字段
        $(this).find('[required]').each(function () {
            if (!$(this).val().trim()) {
                valid = false;
                $(this).addClass('error');
                $(this).after('<span class="error-message">此字段为必填项</span>');
            } else {
                $(this).removeClass('error');
            }
        });

        // 验证邮箱格式
        var email = $('#contact_email').val();
        if (email && !isValidEmail(email)) {
            valid = false;
            $('#contact_email').addClass('error');
            $('#contact_email').after('<span class="error-message">请输入有效的邮箱地址</span>');
        }

        if (!valid) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.error').first().offset().top - 100
            }, 500);
        }
    });

    // 邮箱验证函数
    function isValidEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    //alert(theme_ajax.is_product_page)

    // 分类过滤点击事件
    $('.category-filter a').click(function (e) {
        e.preventDefault();

        var category = $(this).data('category');

        var currentPage = 1; // 重置为第一页

        // 更新活动状态
        $('.category-filter a').removeClass('active');
        $(this).addClass('active');

        // 显示加载动画
        $('#loading').show();

        // 发送AJAX请求
        $.ajax({
            url: theme_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_products',
                category: category,
                page: currentPage,
                nonce: theme_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    // 更新产品列表
                    $('#products-container').html(response.data.html);

                    // 更新页面标题
                    updatePageTitle(category);

                    // 更新URL（不刷新页面）
                    updateBrowserURL(category);

                    // 重新绑定分页事件
                    bindPaginationEvents();
                } else {
                    alert('过滤失败，请重试。');
                }
                $('#loading').hide();
            },
            error: function () {
                alert('请求失败，请检查网络连接。');
                $('#loading').hide();
            }
        });
    });

    // 绑定分页点击事件
    function bindPaginationEvents() {
        $('.ajax-pagination a').click(function (e) {
            e.preventDefault();
            
            var href = $(this).attr('href');
            var page = 1;
            
            // 从URL中提取页码
            var match = href.match(/page\/(\d+)/);
            if (match) {
                page = parseInt(match[1]);
            } else {
                // 尝试从查询参数中提取
                var urlParams = new URLSearchParams(href.split('?')[1]);
                if (urlParams.has('paged')) {
                    page = parseInt(urlParams.get('paged'));
                }
            }

            // 获取当前分类
            var category = $('.category-filter a.active').data('category') || 'all';
            
            // 显示加载动画
            //$('#loading').show();

            // 发送AJAX请求
            $.ajax({
                url: theme_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_products',
                    category: category,
                    page: page,
                    nonce: theme_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $('#products-container').html(response.data.html);
                       
                        // 滚动到产品列表顶部
                        $('html, body').animate({
                            scrollTop: $('#products-container').offset().top - 100
                        }, 500);

                        bindPaginationEvents();
                    }
                    //$('#loading').hide();
                },
                error: function () {
                    alert('分页加载失败。');
                    $('#loading').hide();
                }
            });
        });
    }

    // 初始绑定
    bindPaginationEvents();

    // 重置过滤器
    $(document).on('click', '.btn-reset-filter', function (e) {
        e.preventDefault();
        $('.category-filter a[data-category="all"]').click();
    });



    // 平滑滚动
    $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function (event) {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '')
            && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                event.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top
                }, 1000);
            }
        }
    });
});





// 更新浏览器URL（不刷新页面）
function updateBrowserURL(category) {
    var baseUrl = window.location.origin + window.location.pathname;
    var newUrl = baseUrl;

    if (category !== 'all') {
        newUrl += '?category=' + encodeURIComponent(category);
    }

    // 更新浏览器历史记录
    history.pushState({ category: category }, '', newUrl);
}

// 处理浏览器前进/后退按钮
window.addEventListener('popstate', function (event) {
    if (event.state && event.state.category) {
        var category = event.state.category;
        $('.category-filter a').removeClass('active');
        $('.category-filter a[data-category="' + category + '"]').addClass('active').click();
    }
});


// AJAX产品过滤处理
function filterProducts(category) {
    jQuery.ajax({
        url: theme_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'filter_products',
            category: category,
            nonce: theme_ajax.nonce
        },
        success: function (response) {
            if (response.success) {
                jQuery('#products-container').html(response.data);
            }
        }
    });
}
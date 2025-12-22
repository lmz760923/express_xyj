<?php 
/*
Template Name: 首页
*/
get_header(); ?>

<!-- 首页横幅 -->
<section class="home-banner">
    <div class="container">
        <div class="banner-content">
                        
            <a href="/产品" class="btn-primary">查看产品</a>
            <a href="/关于我们" class="btn-secondary">关于我们</a>
        </div>
    </div>
</section>

<div class="container">
    <div class="content-wrapper">
    <main class="main-content">

<?php echo do_shortcode('[featured_products_slider]');?>
				
			<!--固定展示重点新闻-->
<style>
/* 全局重置 - 确保WordPress不截断内容 */
.ht-company-profile * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    overflow: visible !important;
    height: auto !important;
    max-height: none !important;
}

/* 公司概况核心样式 */
.ht-company-profile {
    padding: 40px 0;
    font-family: "Microsoft Yahei", sans-serif;
    color: #333;
}

.ht-container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

/* 标题区域 */
.ht-profile-header {
    text-align: center;
    margin-bottom: 60px;
}

.ht-profile-title {
    font-size: clamp(24px, 3vw, 40px);
    font-weight: 700;
    color: #165DFF;
    margin-bottom: 15px;
}

.ht-profile-divider {
    width: 80px;
    height: 4px;
    background-color: #FF7D00;
    margin: 0 auto 25px;
}

.ht-profile-desc {
    font-size: 16px;
    color: #475569;
    line-height: 1.8;
    max-width: 700px;
    margin: 0 auto;
}

/* 内容区域 */
.ht-profile-content {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

@media (min-width: 768px) {
    .ht-profile-content {
        flex-direction: row;
        align-items: center;
        gap: 60px;
    }
    .ht-profile-intro, .ht-profile-timeline {
        flex: 1;
    }
}

/* 企业简介 */
.ht-profile-intro h3 {
    font-size: 20px;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 20px;
}

.ht-profile-intro p {
    font-size: 16px;
    color: #475569;
    line-height: 1.8;
    margin-bottom: 25px;
}

/* 发展历程 */
.ht-profile-timeline {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    padding: 30px;
}

.ht-profile-timeline h3 {
    font-size: 20px;
    font-weight: 600;
    color: #1E293B;
    margin-bottom: 30px;
    text-align: center;
}

.ht-timeline-list {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.ht-timeline-item {
    display: flex;
    align-items: flex-start;
}

.ht-timeline-date {
    width: 60px;
    height: 60px;
    background: rgba(22, 93, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #165DFF;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.ht-timeline-content {
    margin-left: 20px;
    padding-top: 5px;
}

.ht-timeline-content h4 {
    font-size: 16px;
    font-weight: 500;
    color: #1E293B;
    margin-bottom: 8px;
}

.ht-timeline-content p {
    font-size: 14px;
    color: #475569;
    line-height: 1.6;
}

/* 移动端适配 */
@media (max-width: 767px) {
    .ht-company-profile {
        padding: 20px 0;
    }
    .ht-profile-header {
        margin-bottom: 40px;
    }
    .ht-profile-timeline {
        padding: 20px;
    }
    .ht-timeline-date {
        width: 50px;
        height: 50px;
        font-size: 12px;
    }
    .ht-timeline-content {
        margin-left: 15px;
    }
}
</style>


<!-- 核心内容模块 -->
<div class="ht-company-profile">
    <div class="ht-container">
        <!-- 头部（阅读更多前可见） -->
        <div class="ht-profile-header">
            <h2 class="ht-profile-title">公司概况</h2>
            <div class="ht-profile-divider"></div>
            <p class="ht-profile-desc">
                深耕非标自动化领域，秉持"科技赋能装配，智能驱动生产"的核心理念
            </p>
        </div>

        <!-- WordPress 阅读更多标签（关键：单独一行，无嵌套） -->
        <!--more-->

        <!-- 主体内容（阅读更多后显示） -->
        <div class="ht-profile-content">
            <!-- 企业简介 -->
            <div class="ht-profile-intro">
                <h3>企业简介</h3>
                <p>
                    惠州市鸿拓智能装备有限公司（原中辉凯达自动化科技）是行业内的新锐企业，专注于非标自动化设备研发。公司团队成员拥有十多年非标设备领域经验，精心打造全链路智能设备解决方案，助力企业突破生产困境，迈向高效、精准、柔性的制造新时代。
                </p>
                <p>
                    公司研发的智能检测系统，集成先进的工业视觉检测与自动化测试模块，已成功应用于汽车零部件及电子元件制造等关键领域。在密封件自动化领域，坚持"专而精"的发展道路，聚焦"O"型圈细分领域，提供专业高效的自动化组装方案。
                </p>
            </div>

            <!-- 发展历程 -->
            <div class="ht-profile-timeline">
                <h3>发展历程</h3>
                <div class="ht-timeline-list">
                    <div class="ht-timeline-item">
                        <div class="ht-timeline-date">2024.5</div>
                        <div class="ht-timeline-content">
                            <h4>公司注册成立</h4>
                            <p>惠州市中辉凯达自动化科技有限公司在惠城区正式注册</p>
                        </div>
                    </div>
                    <div class="ht-timeline-item">
                        <div class="ht-timeline-date">2024.7</div>
                        <div class="ht-timeline-content">
                            <h4>扩大生产场地</h4>
                            <p>在仲恺高新区华涛科技园租赁1000平米生产及办公场地</p>
                        </div>
                    </div>
                    <div class="ht-timeline-item">
                        <div class="ht-timeline-date">2024.10</div>
                        <div class="ht-timeline-content">
                            <h4>首台设备交付</h4>
                            <p>成功交付首台蒸汽轴全自动组装设备，赢得客户高度认可</p>
                        </div>
                    </div>
                    <div class="ht-timeline-item">
                        <div class="ht-timeline-date">2025.3</div>
                        <div class="ht-timeline-content">
                            <h4>完善产业链</h4>
                            <p>组建自有加工团队，配备CNC、线切割等精密加工设备</p>
                        </div>
                    </div>
                    <div class="ht-timeline-item">
                        <div class="ht-timeline-date">2025.11</div>
                        <div class="ht-timeline-content">
                            <h4>公司更名升级</h4>
                            <p>正式更名为"惠州市鸿拓智能装备有限公司"，战略升级</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 补充说明（可选） -->
<div style="width:90%; max-width:1200px; margin:40px auto; font-size:16px; color:#666; line-height:1.8;">
    <p>鸿拓智能始终以技术创新为核心，以客户需求为导向，致力于为制造业客户提供高性价比的非标自动化解决方案，持续推动行业智能化升级。</p>
</div>
            
            <!-- 最新新闻 -->
            <section class="latest-news">
                <h2>最新动态</h2>
                <div class="news-list">
                    <?php
                    $latest_news = new WP_Query(array(
                        'posts_per_page' => 3
                    ));
                    
                    if ($latest_news->have_posts()):
                        while ($latest_news->have_posts()): $latest_news->the_post();
                    ?>
                        <article class="news-item">
                            <h3>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <div class="news-meta">
                                <span class="date"><?php echo get_the_date(); ?></span>
                            </div>
                            <div class="news-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else:
                    ?>
                        <p>暂无新闻。</p>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- 公司简介 -->
            <section class="company-intro">
                <h2>关于我们</h2>
                <p>我们是一家专业的企业解决方案提供商，致力于为客户提供最优质的产品和服务。</p>
                <a href="/关于我们" class="btn-primary">了解更多</a>
            </section>
</main>
        
        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>

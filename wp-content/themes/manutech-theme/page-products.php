<?php
/*
Template Name: 产品中心
*/
get_header();
?>

<main>
    <section class="page-hero">
        <div class="container">
            <h1>产品中心</h1>
            <p>围绕不同制造场景，提供标准设备与可扩展模块。</p>
        </div>
    </section>

    <section class="page-content">
        <div class="container">
            <div class="grid grid-3">
                <article class="card"><h3>精密装配线</h3><p>适用于高一致性批量装配，具备追溯与防错能力。</p><p class="muted">节拍范围：8-45s/件 | 兼容多型号换型</p></article>
                <article class="card"><h3>高速包装设备</h3><p>面向食品与日化行业，稳定高速，低停机率设计。</p><p class="muted">速度范围：120-380 包/分钟</p></article>
                <article class="card"><h3>在线检测工站</h3><p>视觉与传感器融合检测，保障出厂良率与一致性。</p><p class="muted">缺陷识别：划伤、异物、漏装、错位</p></article>
                <article class="card"><h3>机器人应用单元</h3><p>包含上下料、打磨、码垛等单元，快速部署。</p><p class="muted">支持 6 轴机械臂与协作机器人</p></article>
                <article class="card"><h3>自动化物流系统</h3><p>AGV、输送线、立体库协同，打通厂内物流瓶颈。</p><p class="muted">可对接 WMS/MES，实现任务闭环</p></article>
                <article class="card"><h3>数字化看板系统</h3><p>实时呈现 OEE、良率、能耗等关键运营指标。</p><p class="muted">支持班组、产线、工厂多层级统计</p></article>
            </div>

            <section class="section-block">
                <h2 class="section-title">选型建议</h2>
                <div class="grid grid-3">
                    <article class="card"><h3>新建产线</h3><p>优先选择模块化设备，便于后续扩产和工艺升级。</p></article>
                    <article class="card"><h3>老线改造</h3><p>建议采用分段替换与并行验证方案，降低停产风险。</p></article>
                    <article class="card"><h3>多品类制造</h3><p>重点关注快换治具、视觉参数模板和工艺配方管理。</p></article>
                </div>
            </section>

            <section class="section-block">
                <div class="cta-banner">
                    <h2>需要产品手册与技术参数？</h2>
                    <p>提交应用场景、产能目标与厂房条件，我们将提供匹配清单。</p>
                    <a class="btn btn-primary" href="<?php echo esc_url(manutech_get_page_url_by_slug('contact')); ?>">获取产品资料包</a>
                </div>
            </section>
        </div>
    </section>
</main>

<?php get_footer(); ?>

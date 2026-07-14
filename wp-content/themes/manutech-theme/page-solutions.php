<?php
/*
Template Name: 解决方案
*/
get_header();
?>

<main>
    <section class="page-hero">
        <div class="container">
            <h1>行业解决方案</h1>
            <p>以“诊断-设计-实施-运维”闭环交付，持续提升制造竞争力。</p>
        </div>
    </section>

    <section class="page-content">
        <div class="container">
            <div class="grid grid-3">
                <article class="card"><h3>产线升级改造</h3><p>在不停产或低停产条件下，完成节拍与质量能力升级。</p></article>
                <article class="card"><h3>新工厂自动化规划</h3><p>从厂房布局、物流路径到设备配置的全链路规划。</p></article>
                <article class="card"><h3>质量追溯系统集成</h3><p>设备层数据采集与 MES 对接，支持批次与序列化追溯。</p></article>
                <article class="card"><h3>节能降耗优化</h3><p>通过工艺参数优化与设备协同控制，降低单位能耗。</p></article>
                <article class="card"><h3>海外产线复制</h3><p>输出标准化技术包与远程运维，缩短跨区域投产周期。</p></article>
                <article class="card"><h3>维保服务体系</h3><p>备件管理 + 远程诊断 + 驻场服务，保障长期稳定运行。</p></article>
            </div>

            <section class="section-block">
                <h2 class="section-title">实施保障机制</h2>
                <div class="two-col">
                    <article class="card">
                        <h3>交付保障</h3>
                        <ul class="list-clean">
                            <li>里程碑验收制度，阶段成果可视化</li>
                            <li>风险清单提前识别，专项预案闭环跟踪</li>
                            <li>工艺、机械、电气、软件多专业协同评审</li>
                        </ul>
                    </article>
                    <article class="card">
                        <h3>投产保障</h3>
                        <ul class="list-clean">
                            <li>操作与维保双培训，减少爬坡期损耗</li>
                            <li>驻场支持 + 远程监控，快速定位异常</li>
                            <li>按月复盘 OEE 与质量指标，持续优化</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="section-block">
                <div class="cta-banner">
                    <h2>需要行业专属解决方案演示？</h2>
                    <p>我们可基于你的工艺与产能目标，输出投资回报与实施排期建议。</p>
                    <a class="btn btn-primary" href="<?php echo esc_url(manutech_get_page_url_by_slug('contact')); ?>">申请方案评估</a>
                </div>
            </section>
        </div>
    </section>
</main>

<?php get_footer(); ?>

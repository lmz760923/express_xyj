<?php
/*
Template Name: 关于我们
*/
get_header();
?>

<main>
    <section class="page-hero">
        <div class="container">
            <h1>关于我们</h1>
            <p>专注设备智造与工业自动化，为客户创造长期价值。</p>
        </div>
    </section>

    <section class="page-content">
        <div class="container">
            <div class="two-col">
                <article class="card">
                    <h3>公司简介</h3>
                    <p>智造装备科技成立于 2006 年，服务覆盖华东、华南、华北及海外市场。我们拥有机械、电气、软件与工艺复合型团队，具备从单机研发到整线交付的一体化能力。</p>
                    <h3>企业愿景</h3>
                    <p>成为离散制造领域可信赖的自动化合作伙伴，让每条产线更高效、更稳定、更智能。</p>
                </article>
                <article class="card">
                    <h3>核心能力</h3>
                    <ul class="list-clean">
                        <li>非标自动化装备研发与制造</li>
                        <li>整线集成与跨系统数据打通</li>
                        <li>生产质量追溯与数字化运营</li>
                        <li>全生命周期运维与技术升级</li>
                    </ul>
                </article>
            </div>

            <section class="section-block">
                <h2 class="section-title">发展里程碑</h2>
                <div class="timeline compact">
                    <div class="step card"><h3>2006</h3><p>公司成立，聚焦装配自动化设备研发。</p></div>
                    <div class="step card"><h3>2013</h3><p>建立整线集成事业部，覆盖汽车零部件与3C行业。</p></div>
                    <div class="step card"><h3>2018</h3><p>推出视觉检测与追溯平台，实现软硬件协同交付。</p></div>
                    <div class="step card"><h3>2024</h3><p>形成新能源、汽车、消费电子多行业解决方案矩阵。</p></div>
                </div>
            </section>

            <section class="section-block">
                <div class="cta-banner">
                    <h2>欢迎预约工厂参观与技术交流</h2>
                    <p>我们可结合你的业务目标，安排工艺专家进行一对一评估。</p>
                    <a class="btn btn-primary" href="<?php echo esc_url(manutech_get_page_url_by_slug('contact')); ?>">立即预约</a>
                </div>
            </section>
        </div>
    </section>
</main>

<?php get_footer(); ?>

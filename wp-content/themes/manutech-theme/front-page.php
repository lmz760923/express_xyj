<?php
/*
Template Name: 企业首页
*/
get_header();
?>

<main>
    <section class="hero">
        <div class="container">
            <h1 class="fade-in">面向未来工厂的高端装备与智能产线解决方案</h1>
            <p class="fade-in delay-1">从单机设备到整线交付，我们为新能源、汽车零部件、3C 电子与食品包装行业提供高可靠、高节拍、可追溯的制造系统。</p>
            <div class="cta-row fade-in delay-2">
                <a class="btn btn-primary" href="<?php echo esc_url(manutech_get_page_url_by_slug('contact')); ?>">获取方案与报价</a>
                <a class="btn btn-outline" href="<?php echo esc_url(manutech_get_page_url_by_slug('products')); ?>">查看产品中心</a>
            </div>

            <div class="kpi-strip">
                <div class="kpi"><strong>20+</strong><span>年行业沉淀</span></div>
                <div class="kpi"><strong>300+</strong><span>交付产线项目</span></div>
                <div class="kpi"><strong>98%</strong><span>客户复购率</span></div>
                <div class="kpi"><strong>7x24</strong><span>响应式运维</span></div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">核心产品矩阵</h2>
            <p class="section-subtitle">标准化设备 + 模块化工站 + 非标定制能力，覆盖生产全流程。</p>
            <div class="grid grid-3">
                <article class="card">
                    <h3>自动装配设备</h3>
                    <p>支持多品类共线生产，快速换型，内置质量追溯与参数闭环控制。</p>
                </article>
                <article class="card">
                    <h3>视觉检测系统</h3>
                    <p>多相机高精度检测，缺陷识别算法可持续训练，提升一次通过率。</p>
                </article>
                <article class="card">
                    <h3>智能物流单元</h3>
                    <p>AGV/AMR 与立体仓协同调度，实现从上料到入库的自动化流转。</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">行业解决方案</h2>
            <p class="section-subtitle">围绕效率、质量、能耗三大指标，构建可量化收益的改造路径。</p>
            <div class="grid grid-3">
                <article class="card">
                    <h3>新能源电池</h3>
                    <p>电芯装配、模组 Pack 自动化产线，满足高安全标准与节拍要求。</p>
                </article>
                <article class="card">
                    <h3>汽车零部件</h3>
                    <p>发动机、底盘与热管理部件产线集成，兼容多型号柔性生产。</p>
                </article>
                <article class="card">
                    <h3>消费电子</h3>
                    <p>高精密贴装、锁附、测试、包装一体化，支持 MES/ERP 对接。</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-soft">
        <div class="container">
            <h2 class="section-title">项目交付流程</h2>
            <p class="section-subtitle">以里程碑节点管理质量与周期，确保方案可落地、可复制、可持续优化。</p>
            <div class="timeline">
                <div class="step card">
                    <h3>01 现场诊断</h3>
                    <p>工程团队调研工艺、设备与人员协同现状，识别瓶颈工序与关键损失点。</p>
                </div>
                <div class="step card">
                    <h3>02 方案设计</h3>
                    <p>输出布局、节拍、投资回报与风险评估，明确硬件选型与系统接口标准。</p>
                </div>
                <div class="step card">
                    <h3>03 制造交付</h3>
                    <p>按阶段执行制造、联调与 FAT/SAT 验收，保障设备到线即投产。</p>
                </div>
                <div class="step card">
                    <h3>04 运维优化</h3>
                    <p>依托远程监控与驻场支持持续优化 OEE，形成闭环改善机制。</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">典型客户价值</h2>
            <div class="grid grid-3">
                <article class="card">
                    <h3>节拍提升 32%</h3>
                    <p>通过工位重构与节拍均衡，单班产出显著提升，产能瓶颈得到缓解。</p>
                </article>
                <article class="card">
                    <h3>不良率下降 41%</h3>
                    <p>视觉检测与参数防错联动，关键缺陷前移拦截，降低返工与报废成本。</p>
                </article>
                <article class="card">
                    <h3>回本周期 12-18 月</h3>
                    <p>结合投资模型与运维策略，帮助企业在可控周期内实现自动化收益。</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta-banner">
                <h2>准备启动你的产线升级项目？</h2>
                <p>留下工艺需求与目标产能，我们将在 24 小时内提供初步技术路线。</p>
                <div class="cta-row">
                    <a class="btn btn-primary" href="<?php echo esc_url(manutech_get_page_url_by_slug('contact')); ?>">预约技术沟通</a>
                    <a class="btn btn-outline-dark" href="<?php echo esc_url(manutech_get_page_url_by_slug('solutions')); ?>">查看行业方案</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>

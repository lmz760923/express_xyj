<?php
/*
Template Name: 关于我们页面
*/
get_header();

// 显示表单提交成功消息
if (isset($_GET['submitted']) && $_GET['submitted'] == 'true') {
    $success_message = get_transient('contact_form_success');
    if ($success_message):
?>
        <div class="form-success-message">
            <div class="container">
                <?php echo esc_html($success_message); ?>
            </div>
        </div>
<?php
        delete_transient('contact_form_success');
    endif;
}
?>

<div class="container">
    <div class="content-wrapper">
        <main class="main-content">
            <article class="about-page">
                <header class="page-header">
                    
                </header>

                
                <div class="page-content">
                
                <?php the_content(); ?>
                </div>
                    



    <!-- 产品与技术 -->
    <section id="products">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">产品与技术</h2>
                <div class="section-divider"></div>
                <p class="section-desc">
                    标准化机型与非标定制开发结合，提供全方位智能装备解决方案
                </p>
            </div>

            <!-- 核心业务模块 -->
            <div class="core-business">
                <h3 class="core-business-title">核心业务模块</h3>
                <div class="core-business-grid">
                    <div class="business-card">
                        <div class="card-icon-box">
                            <i class="fa fa-cube"></i>
                        </div>
                        <h4 class="card-title">标准化机型</h4>
                        <ul class="card-list">
                            <li>
                                <i class="fa fa-check"></i>
                                <span>"O"型圈自动组装设备：快速精准组装，最高效率1500 PCS/H</span>
                            </li>
                            <li>
                                <i class="fa fa-check"></i>
                                <span>自动锁螺丝机：桌面式/落地式可选，锁付合格率≥99.5%</span>
                            </li>
                            <li>
                                <i class="fa fa-check"></i>
                                <span>视觉筛选机：AI+机器视觉，检测速度30-1000件/分钟</span>
                            </li>
                        </ul>
                    </div>
                    <div class="business-card">
                        <div class="card-icon-box">
                            <i class="fa fa-sliders"></i>
                        </div>
                        <h4 class="card-title">非标定制开发</h4>
                        <ul class="card-list">
                            <li>
                                <i class="fa fa-check"></i>
                                <span>组装类非标定制：量身定制自动化组装设备，实现高度自动化</span>
                            </li>
                            <li>
                                <i class="fa fa-check"></i>
                                <span>检测类非标定制：定制化检测设备，全方位高精度检测</span>
                            </li>
                            <li>
                                <i class="fa fa-check"></i>
                                <span>测试类非标定制：专业测试解决方案，保障产品质量安全</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 产品详情 -->
            <div class="product-grid">
                <!-- O型圈自动组装设备 -->
                <div class="product-card">
                    <div class="product-img-box">
                        <i class="fa fa-cogs"></i>
                    </div>
                    <div class="product-content">
                        <h4>"O"型圈自动组装设备</h4>
                        <ul>
                            <li>• 自动供料、装配，漏装智能识别</li>
                            <li>• 稳定抓取各类O型圈，零损伤</li>
                            <li>• 应对深孔、窄槽等高难度装配</li>
                            <li>• 模块化设计，成本可控</li>
                            <li>• 最高效率1500 PCS/H</li>
                        </ul>
						<!--
                        <a href="#contact" class="product-link">
                            了解详情 <i class="fa fa-arrow-right"></i>
                        </a>
						-->
                    </div>
                </div>

                <!-- 自动锁螺丝机 -->
                <div class="product-card">
                    <div class="product-img-box">
                        <i class="fa fa-wrench"></i>
                    </div>
                    <div class="product-content">
                        <h4>自动锁螺丝机</h4>
                        <ul>
                            <li>• 全流程自动化，效率提升3-10倍</li>
                            <li>• 支持M1-M8多规格螺丝，多种头型</li>
                            <li>• 扭矩控制，漏锁/滑牙报警</li>
                            <li>• 数据追溯，适配精益生产</li>
                            <li>• 桌面式/落地式等多结构可选</li>
                        </ul>
						<!--
                        <a href="#contact" class="product-link">
                            了解详情 <i class="fa fa-arrow-right"></i>
                        </a>
						-->
                    </div>
                </div>

                <!-- 视觉筛选机 -->
                <div class="product-card">
                    <div class="product-img-box">
                        <i class="fa fa-eye"></i>
                    </div>
                    <div class="product-content">
                        <h4>视觉筛选机</h4>
                        <ul>
                            <li>• AI+机器视觉，高速精准检测</li>
                            <li>• 覆盖尺寸精度、外观缺陷等检测项</li>
                            <li>• 自动检测-判定-分类，无需人工</li>
                            <li>• 数据统计与历史追溯功能</li>
                            <li>• 检测速度30-1000件/分钟</li>
                        </ul>
						<!--
                        <a href="#contact" class="product-link">
                            了解详情 <i class="fa fa-arrow-right"></i>
                        </a>
						-->
                    </div>
                </div>
            </div>

            <!-- 定制开发项目 -->
            <div class="custom-case">
                <h4 class="custom-case-title">非标定制开发项目案例</h4>
                <div class="case-grid">
                    <div class="case-item">
                        <i class="fa fa-fire"></i>
                        <p>高温老化柜</p>
                    </div>
                    <div class="case-item">
                        <i class="fa fa-search"></i>
                        <p>AOI检测设备</p>
                    </div>
                    <div class="case-item">
                        <i class="fa fa-shield"></i>
                        <p>油封外观检查设备</p>
                    </div>
                    <div class="case-item">
                        <i class="fa fa-tachometer"></i> <!-- 替换不存在的fa-tester -->
                        <p>ATE测试设备</p>
                    </div>
                    <div class="case-item">
                        <i class="fa fa-cog"></i>
                        <p>蒸汽轴全自动组装设备</p>
                    </div>
                    <div class="case-item">
                        <i class="fa fa-plug"></i>
                        <p>端子组装设备</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 服务与未来 -->
    <section id="service">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">服务与未来</h2>
                <div class="section-divider"></div>
                <p class="section-desc">
                    以客户为中心，提供全方位一站式服务，引领行业发展潮流
                </p>
            </div>

            <div class="service-grid">
                <div>
                    <h3 class="service-title">服务理念与优势</h3>
                    <div class="service-list">
                        <div class="service-item">
                            <div class="service-icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="service-content">
                                <h4>专业售前团队</h4>
                                <p>深入了解客户需求，提供专业技术咨询和方案设计</p>
                            </div>
                        </div>
                        <div class="service-item">
                            <div class="service-icon">
                                <i class="fa fa-headphones"></i>
                            </div>
                            <div class="service-content">
                                <h4>完善售后服务</h4>
                                <p>技术精湛、响应迅速的售后团队，及时解决设备使用问题</p>
                            </div>
                        </div>
                        <div class="service-item">
                            <div class="service-icon">
                                <i class="fa fa-handshake-o"></i>
                            </div>
                            <div class="service-content">
                                <h4>一站式服务</h4>
                                <p>从需求沟通、方案设计到设备交付、售后维护全流程服务</p>
                            </div>
                        </div>
                        <div class="service-item">
                            <div class="service-icon">
                                <i class="fa fa-bolt"></i>
                            </div>
                            <div class="service-content">
                                <h4>快速响应机制</h4>
                                <p>高效沟通，快速响应客户需求，缩短项目周期</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="service-title">未来展望</h3>
                    <div class="future-card">
                        <ul class="future-list">
                            <li class="future-item">
                                <div class="future-number">01</div>
                                <div class="future-content">
                                    <h4>技术创新升级</h4>
                                    <p>加大研发投入，提升产品性能和质量，推出更多创新性产品</p>
                                </div>
                            </li>
                            <li class="future-item">
                                <div class="future-number">02</div>
                                <div class="future-content">
                                    <h4>市场拓展合作</h4>
                                    <p>加强国内外客户合作，为更多企业提供优质智能装备和解决方案</p>
                                </div>
                            </li>
                            <li class="future-item">
                                <div class="future-number">03</div>
                                <div class="future-content">
                                    <h4>引领行业发展</h4>
                                    <p>推动非标自动化设备行业标准化、模块化、智能化发展</p>
                                </div>
                            </li>
                            <li class="future-item">
                                <div class="future-number">04</div>
                                <div class="future-content">
                                    <h4>赋能智能制造</h4>
                                    <p>为推动智能制造产业发展做出更大贡献，助力制造业转型升级</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

                    <!-- 联系我们表单 -->
                    <section class="contact-form-section">
                        <h2>联系我们</h2>
                        <p>请填写以下表单，我们会尽快与您联系。</p>
                        
                        <form method="post" class="contact-form" id="contactForm">
                            <?php wp_nonce_field('contact_form_action', 'contact_nonce'); ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact_name">姓名 *</label>
                                    <input type="text" id="contact_name" name="contact_name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_company">公司名称</label>
                                    <input type="text" id="contact_company" name="contact_company">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact_email">邮箱 *</label>
                                    <input type="email" id="contact_email" name="contact_email" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_phone">电话 *</label>
                                    <input type="tel" id="contact_phone" name="contact_phone" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_message">留言内容 *</label>
                                <textarea id="contact_message" name="contact_message" rows="6" required></textarea>
                            </div>
                            
                            <div class="form-submit">
                                <button type="submit" name="contact_submit" class="btn-primary">
                                    提交信息
                                </button>
                            </div>
                        </form>
                    </section>


                  

                </div>
            </article>
        </main>
        
        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>
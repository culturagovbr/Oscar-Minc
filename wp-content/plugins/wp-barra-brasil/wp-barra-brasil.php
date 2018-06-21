<?php
/*
 * Plugin Name: Wp Barra Brasil
 * Plugin URI: https://github.com/redelivre/wp-barra-brasil
 * Description: Implementa a barra brasil no wordpress (http://barra.governoeletronico.gov.br/)
 * Version: 0.1.0
 * Author: Redelivre
 * Author URI: http://redelivre.org.br
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * */

// PHP 5.3 and later:
namespace WpBarraBrasil;

class WpBarraBrasil
{
	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'js'));
		add_action('wp_enqueue_scripts', array($this, 'css'));
		add_action('wp_head', array($this, 'head'));
		add_action('wp_footer', array($this, 'footer'));
		add_action( 'customize_register', array($this, 'customize_register'));
	}
	
	/**
	 * Enqueue JavaScritps files and configs
	 */
	public function js()
	{
		wp_enqueue_script('WpBarraBrasil', plugin_dir_url(__FILE__).'/frontend/js/WpBarraBrasil.js', array('jquery'), '0.1.0', true);
		wp_enqueue_script('BarraBrasil', '//barra.brasil.gov.br/barra.js', array('WpBarraBrasil'), '0.1.0', true);
		
		$data = array(
			'element_to_prepend' => apply_filters('wp-barra-brasil-position-element', get_theme_mod('WpBarraBrasilHeaderElement', 'BODY'))
		);
		
		wp_localize_script('WpBarraBrasil', 'WpBarraBrasil', $data);
	}
	
	/**
	 * Enqueue css files
	 */
	public function css()
	{
		wp_enqueue_style('WpBarraBrasil', plugin_dir_url(__FILE__).'/frontend/css/WpBarraBrasil.css');
	}
	
	public function footer()
	{
		echo '<div id="footer-brasil" class="'.get_theme_mod('WpBarraBrasilFooterColor', 'verde').'"></div>';
	}
	
	public function head()
	{
		$theme_opt = get_theme_mod('WpBarraBrasilServiceNumber', '');
		if(!empty($theme_opt))
		{
			echo '<meta property="creator.productor" content="http://estruturaorganizacional.dados.gov.br/id/unidade-organizacional/'.$theme_opt.'">';
		}
	}
	
	/**
	 * Add Customize Options and settings
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	function customize_register( $wp_customize )
	{
		/*
		 * 
		 */
		$wp_customize->add_section( 'WpBarraBrasil', array(
			'title'    => __( 'Barra Brasil', 'WpBarraBrasil' ),
			'priority' => 30,
		) );
	
		// Element to append html content
		$wp_customize->add_setting( 'WpBarraBrasilHeaderElement', array(
			'default'     => 'BODY',
			'capability'    => 'edit_theme_options',
		) );
		
		$wp_customize->add_control( 'WpBarraBrasilHeaderElement', array(
			'label'      => __( 'Elemento HTML onde deve se adionado o código da barra (para id inicie com "#" e classe CSS com ".")', 'WpBarraBrasil'),
			'section'    => 'WpBarraBrasil',
		) );
		
		$wp_customize->add_setting('WpBarraBrasilFooterColor', array(
			'default'        => 'verde'
		));
		
		$themecolors = array(
			'verde' => __('Verde', 'WpBarraBrasil'),
			'amarelo' => __('Amarelo', 'WpBarraBrasil'),
			'azul' => __('Azul', 'WpBarraBrasil'),
			'branco' => __('Branco', 'WpBarraBrasil'),
		);
		
		$wp_customize->add_control( 'WpBarraBrasilFooterColor', array(
			'settings' => 'WpBarraBrasilFooterColor',
			'label'   => __('Selecione a cor do seu tema para adequar ao padrão do rodapé (footer)', 'WpBarraBrasil').':',
			'section'  => 'WpBarraBrasil',
			'type'    => 'select',
			'choices' => $themecolors,
		));
		
		$wp_customize->add_setting('WpBarraBrasilServiceNumber', array(
			'default'        => ''
		));
		
		$wp_customize->add_control( 'WpBarraBrasilServiceNumber', array(
			'label'      => __( 'número correto do órgão no SIORG. Acesse o SIORG e procure pelo seu órgão.', 'WpBarraBrasil').' http://siorg.planejamento.gov.br',
			'section'    => 'WpBarraBrasil',
		) );
	}
	
}

global $WpBarraBrasil;
$WpBarraBrasil = new WpBarraBrasil();

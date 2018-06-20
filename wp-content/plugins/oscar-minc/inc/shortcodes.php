<?php

/**
 * Class Oscar_Minc_Shortcodes
 *
 */
class Oscar_Minc_Shortcodes
{
    public function __construct()
    {
        add_shortcode('oscar-minc', array($this, 'oscar_minc_subscription_form_shortcode'));
        add_shortcode('oscar-register', array($this, 'oscar_minc_auth_form'));
        add_shortcode('oscar-login', array($this, 'oscar_minc_login_form'));
        add_shortcode('oscar-subscriptions', array($this, 'oscar_minc_user_subscriptions'));
        add_shortcode('oscar-upload-video', array($this, 'oscar_minc_video_upload_form'));
        add_shortcode('oscar-password-lost-form', array($this, 'render_password_lost_form'));
    }

    /**
     * Shortcode to show ACF form
     *
     * @param $atts
     * @return string
     */
    public function oscar_minc_subscription_form_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'form-group-id' => '',
            'return' => home_url('/?sent=true#message')
        ), $atts);

        ob_start();

        if( get_post_meta( $_GET['inscricao'], 'movie_attachment_id', true ) ) :

            echo '<p>Sua inscrição está sendo analisada, não é possível editar os dados.</p>';

        else :

            $post_inscricao = empty($_GET['inscricao']) ? 'new_inscricao' : $_GET['inscricao'];

            $settings = array(
                'field_groups' => array($atts['form-group-id']),
                'id' => 'oscar-main-form',
                'post_id' => $post_inscricao,
                'new_post' => array(
                    'post_type' => 'inscricao',
                    'post_status' => 'publish'
                ),
                'updated_message' => 'Inscrição enviada com sucesso.',
                'return' => $atts['return'],
                'submit_value' => 'Salvar dados'
            );
            acf_form($settings);
        endif;

        return ob_get_clean();
    }

    /**
     * Authentication form
     *
     * @param $atts
     * @return string
     */
    public function oscar_minc_auth_form($atts)
    {
		if ($_POST['reg_submit']) {
			$this->validation();
			$this->registration();
		}

		$name = null;
		$email = null;
		$cnpj = null;
		$password = null;

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$name = $current_user->display_name;
			$email = $current_user->user_email;
			$cnpj = OscarMinC::mask(get_user_meta( $current_user->ID, '_user_cnpj', true ), '##.###.###/####-##'); ;
        }

		ob_start();
        if ( !is_user_logged_in() ) : ?>
        <div class="text-right">
            <p>Já possui cadastro? Faça login <b><a href="<?php echo home_url('/login'); ?>">aqui</a>.</b></p>
        </div>
        <?php endif; ?>
        <form id="oscar-register-form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
            <div class="login-form row">
                <div class="form-group col-md-6">
                    <label class="login-field-icon fui-user" for="reg-name">Nome completo</label>
                    <input name="reg_name" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_name']) ? $_POST['reg_name'] : $name); ?>"
                           placeholder="" id="reg-name" <?php echo is_user_logged_in() ? '' : 'required'; ?>/>
                </div>

                <div class="form-group col-md-6">
                    <label class="login-field-icon fui-mail" for="reg-email">Email</label>
                    <input name="reg_email" type="email" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_email']) ? $_POST['reg_email'] : $email); ?>"
                           placeholder="" id="reg-email" <?php echo is_user_logged_in() ? '' : 'required'; ?>/>
                </div>

                <div class="form-group col-md-4">
                    <label class="login-field-icon fui-lock" for="reg-cnpj">CNPJ</label>
                    <input name="cnpj" type="text" class="form-control login-field"
                           value="<?php echo(isset($_POST['cnpj']) ? $_POST['cnpj'] : $cnpj); ?>"
                           placeholder="00.000.000/0000-00" id="reg-cnpj" <?php echo is_user_logged_in() ? '' : 'required'; ?>/>
                </div>

                <div class="form-group col-md-4">
                    <label class="login-field-icon fui-lock" for="reg-pass">Senha</label>
                    <input name="reg_password" type="password" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_password']) ? $_POST['reg_password'] : null); ?>"
                           placeholder="" id="reg-pass" <?php echo is_user_logged_in() ? '' : 'required'; ?>/>
                </div>

                <div class="form-group col-md-4">
                    <label class="login-field-icon fui-lock" for="reg-pass-repeat">Repita a senha</label>
                    <input name="reg_password_repeat" type="password" class="form-control login-field"
                           value="<?php echo(isset($_POST['reg_password_repeat']) ? $_POST['reg_password_repeat'] : null); ?>"
                           placeholder="" id="reg-pass-repeat" <?php echo is_user_logged_in() ? '' : 'required'; ?>/>
                </div>

                <div class="form-group col-md-12 text-right">
                    <input class="btn btn-default" type="submit" name="reg_submit" value="<?php echo is_user_logged_in() ? 'Atualizar' : 'Cadastrar'; ?>"/>
                </div>
            </div>
        <?php if( is_user_logged_in() ): ?>
            <input type="hidden" name="is-updating" value="1">
            <input type="hidden" name="user-id" value="<?php echo $current_user->ID; ?>">
        <?php endif; ?>
        </form>

		<?php return ob_get_clean();
    }

    /**
     * Register validation
     *
     * @return WP_Error
     */
    private function validation()
    {
        $username = $_POST['reg_name'];
        $email = $_POST['reg_email'];
        $cnpj = $_POST['cnpj'];
        $password = $_POST['reg_password'];
        $reg_password_repeat = $_POST['reg_password_repeat'];
		$is_updating = isset( $_POST['is-updating'] ) ? true : false;

		if( !$is_updating ){
			if (empty($username) || empty($password) || empty($email) || empty($cnpj)) {
				return new WP_Error('field', 'Todos os campos são de preenchimento obrigatório.');
			}
        } else {
			if (empty($username) || empty($email) || empty($cnpj)) {
				return new WP_Error('field', 'Todos os campos são de preenchimento obrigatório.');
			}
        }

		if( !$is_updating ){
            if (strlen($password) < 5) {
                return new WP_Error('password', 'A senha está muito curta.');
            }
        } else {
            if ( !empty($password) && strlen($password) < 5) {
                return new WP_Error('password', 'A senha está muito curta.');
            }
        }

        if (!is_email($email)) {
            return new WP_Error('email_invalid', 'O email parece ser inválido');
        }

        if (email_exists($email) && !$is_updating) {
            return new WP_Error('email', 'Este email já sendo utilizado, para cadastrar um novo filme, por favor utilize outro email.');
        }

        if ($password !== $reg_password_repeat) {
            return new WP_Error('password', 'As senhas inseridas são diferentes.');
        }

        if (strlen(str_replace('.', '', str_replace('-', '', str_replace('/', '', $cnpj)))) !== 14) {
            return new WP_Error('cnpj', 'O CNPJ é inválido.');
        }
    }

    /**
     * Register user
     *
     */
    private function registration()
    {
        $username = $_POST['reg_name'];
        $email = $_POST['reg_email'];
        $cnpj = str_replace('.', '', str_replace('-', '', str_replace('/', '', $_POST['cnpj'])));
        $password = $_POST['reg_password'];
        $user_id = $_POST['user-id'];
        $is_updating = isset( $_POST['is-updating'] ) ? true : false;

        $userdata = array(
            'first_name' => esc_attr($username),
            'display_name' => esc_attr($username),
            'user_login' => esc_attr($email),
            'user_email' => esc_attr($email),
            'user_pass' => esc_attr($password)
        );

        $errors = $this->validation();

        if (is_wp_error($errors)) :
            echo '<div class="alert alert-danger">';
            echo '<strong>' . $errors->get_error_message() . '</strong>';
            echo '</div>';
        else :
            if ( $is_updating ) {
				$userdata = array(
					'ID' => $user_id,
					'first_name' => esc_attr($username),
					'display_name' => esc_attr($username),
					'user_login' => esc_attr($email),
					'user_email' => esc_attr($email),
					'user_pass' => esc_attr($password)
				);

				$user_id = wp_update_user($userdata);

				if ( is_wp_error( $user_id ) ) {
					echo '<div class="alert alert-danger">';
					echo '<strong>' . $user_id->get_error_message() . '</strong>';
					echo '</div>';
				} else {
					echo '<div class="alert alert-success">';
					echo 'Cadastro atualizado com sucesso.';
					echo '</div>';
				}
            } else {
				$register_user = wp_insert_user($userdata);
				if (!is_wp_error($register_user)) {
					add_user_meta($register_user, '_user_cnpj', esc_attr($cnpj), true);
					echo '<div class="alert alert-success">';
					echo 'Cadastro realizado com sucesso. Você será redirionado para a tela de login em <b class="time-before-redirect">5</b> segundos, caso isso não ocorra automaticamente, clique <strong><a href="' . home_url('/login') . '">aqui</a></strong>!';
					echo '</div>';
					$_POST = array(); ?>
                    <script type="text/javascript">
                        var counter = 5;
                        var interval = setInterval(function() {
                            counter--;
                            $('.time-before-redirect').text(counter);
                            if (counter === 0) {
                                clearInterval(interval);
                                window.location = '<?php echo home_url("/login"); ?>';
                            }
                        }, 1000);
                    </script>
				<?php } else {
					echo '<div class="alert alert-danger">';
					echo '<strong>' . $register_user->get_error_message() . '</strong>';
					echo '</div>';
				}
            }
        endif;

    }

    /**
     * Login form
     *
     */
    public function oscar_minc_login_form()
    { ?>

        <div class="text-right">
            <p>Ainda não possui cadastro? Faça o seu <b><a href="<?php echo home_url('/cadastro'); ?>">aqui</a>.</b></p>
        </div>

        <?php if ( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) : ?>
        <div class="alert alert-danger" role="alert">
            Erro ao realizar o login. Por favor, verifique as informações e tente novamente
        </div>
        <?php endif;

		if ( isset( $_GET['checkemail'] ) && $_GET['checkemail'] === 'confirm' ) : ?>
            <div class="alert alert-success" role="alert">
                Cheque seu email para recuperar sua senha.
            </div>
		<?php endif;

        wp_login_form(
            array(
                'redirect' => home_url(),
                'form_id' => 'oscar-login-form',
                'label_username' => __('Endereço de e-mail'),
                'value_username' => isset( $_COOKIE['log'] ) ? $_COOKIE['log'] : null
            )
        ); ?>

        <!--<p><a href="<?php /*echo wp_lostpassword_url( home_url() ); */?>" class="forget-password-link" title="Esqueceu a senha?">Esqueceu a senha?</a></p>-->

        <?php
    }

    /**
     * Show users subscriptions
     *
     */
    public function oscar_minc_user_subscriptions()
    {
        $current_user = wp_get_current_user();
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'inscricao',
            'order' => 'ASC',
            'author' => $current_user->ID
        );
        $the_query = new WP_Query( $args );

        if ( $the_query->have_posts() ) { ?>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Data de inscrição</th>
                    <th scope="col">Título do filme</th>
                    <th scope="col">Situação</th>
                    <th scope="col">Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo get_the_date(); ?></td>
                        <td><?php echo get_field('titulo_do_filme') ? get_field('titulo_do_filme') : '-'; ?></td>
                        <td><?php echo get_post_meta( get_the_ID(), 'movie_attachment_id', true ) ? 'Filme enviado' : 'Filme <b>não</b> enviado'; ?></td>
                        <td>
                            <?php if( !get_post_meta( get_the_ID(), 'movie_attachment_id', true ) ): ?>
                                <a href="<?php echo home_url('/inscricao') . '?inscricao=' . get_the_ID(); ?>" class="btn btn-primary btn-sm" role="button" data-toggle="tooltip" data-placement="top" title="Editar inscrição">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="<?php echo home_url('/enviar-video') . '?inscricao=' . get_the_ID(); ?>" class="btn btn-primary btn-sm" role="button" data-toggle="tooltip" data-placement="top" title="Enviar filme">
                                    <i class="fa fa-paper-plane"></i>
                                </a>
                            <?php else: ?>
                                <span data-toggle="tooltip" data-placement="top" title="Solicitar suporte">
                                    <a href="#"
                                       class="ask-for-support-link btn btn-primary btn-sm"
                                       role="button"
                                       data-toggle="modal"
                                       data-target="#support-modal"
                                       data-movie-name="<?php the_field('titulo_do_filme'); ?>"
                                       data-post-id="<?php the_ID(); ?>">
                                        <i class="fa fa-question-circle"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php $i++; endwhile; ?>
                </tbody>
            </table>
            <a href="<?php echo home_url('/inscricao'); ?>" class="btn btn-primary ">Realizar nova inscrição</a>

            <div class="modal fade" id="support-modal" tabindex="-1" role="dialog" aria-labelledby="support-modal-title" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="support-modal-title">Solicitar suporte</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="support-form">
                            <div class="modal-body">
                                <div class="alert alert-success d-none" role="alert"></div>
                                <div class="alert alert-danger d-none" role="alert"></div>
                                <div class="form-fields">
                                    <div class="form-group row">
                                        <label for="movie-name" class="col-sm-2 col-form-label">Filme</label>
                                        <div class="col-sm-10">
                                            <input type="text" readonly class="form-control-plaintext" id="movie-name" value="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="support-reason" class="col-sm-2 col-form-label">Motivo</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="support-reason" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="support-message" class="col-sm-2 col-form-label">Mensagem</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" id="support-message" rows="3" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                            <input type="hidden" id="post-id" name="post-id" value="">
                        </form>
                    </div>
                </div>
            </div>

            <?php wp_reset_postdata();
        } else { ?>
            <a href="<?php echo home_url('/inscricao'); ?>" class="btn btn-primary ">Realizar inscrição</a>
        <?php }
    }

	/**
     * Upload movie form
     *
	 * @return string
	 */
    public function oscar_minc_video_upload_form()
    {
		$oscar_minc_options = get_option('oscar_minc_options');
        ob_start();

        if( !empty($_GET['inscricao']) ): ?>

            <?php if( !get_post_meta( $_GET['inscricao'], 'movie_attachment_id', true ) ): ?>

                <p>Filme: <b><?php echo get_post_meta($_GET['inscricao'], 'titulo_do_filme', true); ?></b>.</p>

                <div id="info-alert" class="alert alert-primary" role="alert">
                    <p>Tamanho máximo para o arquivo de vídeo: <b><?php echo $oscar_minc_options['oscar_minc_movie_max_size']; ?>Gb</b>. Velocidade de conexão mínima sugerida: <b>10Mb</b>.</p>
                    <p>Resolução mínima <b>720p</b>. Formatos permitidos: <b><?php echo $oscar_minc_options['oscar_minc_movie_extensions'] ?></b>.</p>
                </div>

                <div id="error-alert" class="alert alert-danger d-none" role="alert"></div>

                <form id="oscar-video-form" method="post" action="<?php echo get_the_permalink() ?>">
                    <div class="form-group text-center video-drag-area dropzone">
                        <input type="hidden" id="post_id" name="post_id" value="<?php echo $_GET['inscricao']; ?>">
                        <input type="hidden" id="movie_max_size" value="<?php echo intval($oscar_minc_options['oscar_minc_movie_max_size']) * pow(1024,3); ?>">
                        <input type="file" id="oscar-video" name="oscar-video" class="inputfile" accept=".<?php echo str_replace(', ', ', .', $oscar_minc_options['oscar_minc_movie_extensions']); ?>">
                        <label id="oscar-video-btn" for="oscar-video"><i class="fa fa-upload"></i> Selecione seu vídeo</label>
                        <p id="oscar-video-name" class="help-block"></p>
                    </div>
                    <div id="upload-status" class="form-group hidden">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success progress-bar-striped myprogress progress-bar-animated" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                <span class="sr-only">40% Complete (success)</span>
                            </div>
                        </div>
                        <div class="panel panel-default msg"></div>
                    </div>
                    <div class="text-right">
                        <button id="oscar-video-upload-btn" type="submit" class="btn btn-default" disabled>Enviar</button>
                    </div>
                </form>

            <?php else: ?>
                <p>Seu filme foi enviado com sucesso.</p>
            <?php endif ?>

        <?php else: ?>

            <p>Selecione uma inscrição para enviar o vídeo <a href="<?php echo home_url('/minhas-inscricoes'); ?>">aqui.</a></p>

        <?php endif;

        return ob_get_clean();
    }

	/**
	 * A shortcode for rendering the form used to initiate the password reset.
	 *
	 * @param  array   $attributes  Shortcode attributes.
	 * @param  string  $content     The text content for shortcode. Not used.
	 *
	 * @return string  The shortcode output
     *
	 */
	public function render_password_lost_form( $attributes, $content = null )
    { ?>

        <div id="password-lost-form" class="widecolumn">
            <p>Digite seu nome de usuário ou endereço de e-mail. Você receberá um link para criar uma nova senha via e-mail.</p>

            <form id="lostpasswordform" action="<?php echo wp_lostpassword_url(); ?>" method="post">
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="user_login">Email</label>
                        <input type="text" name="user_login" id="user_login" class="form-control login-field">
                    </div>
                    <div class="form-group col-md-12 text-right">
                        <input type="submit" name="submit" class="lostpassword-button btn btn-default" value="Recuperar senha"/>
                    </div>
                </div>
            </form>
        </div>
	<?php }

}
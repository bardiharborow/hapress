<div class="restore-info-root">

	<p class="info">
		<?php esc_html_e('Migrating or restoring your website from a managed backup is really easy.', SNAPSHOT_I18N_DOMAIN); ?>
		<?php esc_html_e('Simply choose the backup you want to use, add your server details and run the automatic restoration tool.', SNAPSHOT_I18N_DOMAIN); ?>
	</p>

	<div class="restore-steps">

		<div class="step requirements active">
			<div class="head">
				<h3><?php esc_html_e('1. Check requirements', SNAPSHOT_I18N_DOMAIN); ?></h3>
			</div>
			<div class="body">
				<p>
					<?php esc_html_e('First, we need to make sure your server, database and files meet our basic requirements.', SNAPSHOT_I18N_DOMAIN); ?>
				</p>
				<div class="requirements-check">
					<div class="requirements-process checking">
						<p class="indicator"><i>...</i></p>
						<p><?php esc_html_e('Checking server reqiurements (only takes a few seconds)...', SNAPSHOT_I18N_DOMAIN); ?></p>
					</div>
					<div class="requirements-process final">

						<section class="webserver">
							<header>
								<h3><?php esc_html_e('Web Server', SNAPSHOT_I18N_DOMAIN); ?></h3>
							</header>
							<article class="system">
								<header>
									<h3><?php esc_html_e('System', SNAPSHOT_I18N_DOMAIN); ?></h3>
								</header>
								<div class="response">
									<p class="result"></p>
									<p class="info">
										<?php
											esc_html_e('Supported web servers: Apache, LiteSpeed, Nginx, Lighttpd, IIS, WebServerX, uWSGI', SNAPSHOT_I18N_DOMAIN);
										?>
									</p>
								</div>
							</article>
						</section>

						<section class="php">
							<header>
								<h3><?php esc_html_e('PHP', SNAPSHOT_I18N_DOMAIN); ?></h3>
							</header>
							<article class="maxtime">
								<header>
									<h3><?php esc_html_e('Max Execution Time', SNAPSHOT_I18N_DOMAIN); ?></h3>
								</header>
								<div class="response">
									<p class="result"></p>
									<p class="info">
										<?php esc_html_e('Issues might occur for larger packages when the [max_execution_time] value in php.ini is too low.', SNAPSHOT_I18N_DOMAIN); ?>
										<?php esc_html_e('The minimum recommended timeout is "150" seconds or higher.', SNAPSHOT_I18N_DOMAIN); ?>
										<?php esc_html_e('An attempt is made to override this value if the server allows it.', SNAPSHOT_I18N_DOMAIN); ?>
										<?php esc_html_e('A value of "0" (recommended) indicates that PHP has no time limits.', SNAPSHOT_I18N_DOMAIN); ?>
									</p>
								</div>
							</article>
							<article class="mysqli">
								<header>
									<h3><?php esc_html_e('MySQLi', SNAPSHOT_I18N_DOMAIN); ?></h3>
								</header>
								<div class="response">
									<p class="result"></p>
									<p class="info">
										<?php esc_html_e('Creating the package does not require the mysqli module.', SNAPSHOT_I18N_DOMAIN); ?>
										<?php esc_html_e('However, the installer.php file requires that the PHP module mysqli be installed on the server it is deplyoed on.', SNAPSHOT_I18N_DOMAIN); ?>
									</p>
								</div>
							</article>
						</section>

						<section class="wordpress">
							<header>
								<h3><?php esc_html_e('WordPress', SNAPSHOT_I18N_DOMAIN); ?></h3>
							</header>
							<article class="version">
								<header>
									<h3><?php esc_html_e('Version', SNAPSHOT_I18N_DOMAIN); ?></h3>
								</header>
								<div class="response">
									<p class="result"></p>
									<p class="info">
										<?php
											esc_html_e('Are we running the latest stable WordPress version?', SNAPSHOT_I18N_DOMAIN);
										?>
									</p>
								</div>
							</article>
						</section>

						<section class="fileset">
							<header>
								<h3><?php esc_html_e('Files', SNAPSHOT_I18N_DOMAIN); ?></h3>
							</header>
							<article class="location">
								<header>
									<h3><?php esc_html_e('Location', SNAPSHOT_I18N_DOMAIN); ?></h3>
								</header>
								<div class="response">
									<p class="result"></p>
									<p class="info">
										<?php
											esc_html_e('This is the location we will be backing up', SNAPSHOT_I18N_DOMAIN);
										?>
									</p>
								</div>
							</article>
						</section>

						<section class="tableset">
							<header>
								<h3><?php esc_html_e('Database', SNAPSHOT_I18N_DOMAIN); ?></h3>
							</header>
							<article class="quantity">
								<header>
									<h3><?php esc_html_e('Tables', SNAPSHOT_I18N_DOMAIN); ?></h3>
								</header>
								<div class="response">
									<p class="result"></p>
									<p class="info">
										<?php
											esc_html_e('This is the number of tables we will be backing up', SNAPSHOT_I18N_DOMAIN);
										?>
									</p>
								</div>
							</article>
						</section>


					</div>
					<p class="failure-info">
						<?php _e('Some requirements have <i>failed</i>, which may result in an installation failure.', SNAPSHOT_I18N_DOMAIN); ?>
						<?php _e('We recommend doing what you can to fix those issues and then <b>re-run the check</b>.', SNAPSHOT_I18N_DOMAIN); ?>
						<?php _e('Alternatively you can proceed ignoring the warnings.', SNAPSHOT_I18N_DOMAIN); ?>
					</p>
				</div>
				<p>
					<button type="button" class="button cancel">
						<?php esc_html_e('Cancel', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
					<button type="button" class="button button-primary check">
						<?php esc_html_e('Check requirements', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
					<button type="button" class="button next">
						<?php esc_html_e('Next', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
					<button type="button" class="button button-primary re-check">
						<?php esc_html_e('Check again!', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
				</p>
			</div>
		</div>

		<div class="step connect">
			<div class="head">
				<h3><?php esc_html_e('2. Connect', SNAPSHOT_I18N_DOMAIN); ?></h3>
			</div>
			<div class="body">
				<p>
					<?php esc_html_e('Choose which folder do you want to restore your website to.', SNAPSHOT_I18N_DOMAIN); ?>
					<?php esc_html_e('We will then automatically restore your website and let you know when it\'s ready', SNAPSHOT_I18N_DOMAIN); ?>
				</p>
				<div class="actionable">
					<input type="hidden" id="archive" name="archive" class="widefat archive" value="<?php echo esc_attr(trailingslashit(wp_normalize_path(sys_get_temp_dir())) . 'archive.zip'); ?>" />
					<?php request_filesystem_credentials(home_url()); ?>
					<p>
						<label for="location">
							<?php esc_html_e('Restore to:', SNAPSHOT_I18N_DOMAIN); ?>
							<input type="text" id="location" name="location" class="widefat location" value="<?php
								//echo esc_attr('d:/tmp/full-test/restore');
								echo apply_filters('snapshot_home_path', get_home_path());
							?>" />
							<em><?php esc_html_e('Full path to restore directory, e.g. /var/www/test/', SNAPSHOT_I18N_DOMAIN); ?></em>
						</label>
					</p>
				</div>
				<p>
					<?php esc_html_e('When you are ready to restore your website, click the button below.', SNAPSHOT_I18N_DOMAIN); ?>
					<?php esc_html_e('This process can take anywhere from 30 seconds to up to 10 minutes depending on the size of your website.', SNAPSHOT_I18N_DOMAIN); ?>

				</p>
				<p>
					<button type="button" class="button cancel">
						<?php esc_html_e('Cancel', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
					<button type="button" class="button button-primary run">
						<?php esc_html_e('Restore', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
				</p>
			</div>
		</div>

		<div class="step progress">
			<div class="head">
				<h3><?php esc_html_e('3. Progress', SNAPSHOT_I18N_DOMAIN); ?></h3>
			</div>
			<div class="body">
				<p>
					<?php esc_html_e('We are now restoring your backup to a fully working website.', SNAPSHOT_I18N_DOMAIN); ?>
					<?php esc_html_e('Please be patient, this process can take between 2 - 20 minutes depending on file size and load, go grab a cup of tea and come back :)', SNAPSHOT_I18N_DOMAIN); ?>
					<br />
					<?php esc_html_e('Please do not leave this page while your backup is being restored.', SNAPSHOT_I18N_DOMAIN); ?>
				</p>
				<div class="progress update">
					<p class="indicator"><i>...</i></p>
					<p class="fetch"><?php esc_html_e('Fetching file...', SNAPSHOT_I18N_DOMAIN); ?></p>
					<p class="process"><?php esc_html_e('Managed backup restoration in progress...', SNAPSHOT_I18N_DOMAIN); ?></p>
					<p class="done"><span><?php esc_html_e('Restoration successfull', SNAPSHOT_I18N_DOMAIN); ?></span></p>
					<p class="error"><span><?php esc_html_e('There was an error restoring your backup', SNAPSHOT_I18N_DOMAIN); ?></span></p>
				</div>
				<p>
					<button type="button" class="button cancel">
						<?php esc_html_e('Close', SNAPSHOT_I18N_DOMAIN); ?>
					</button>
				</p>
			</div>
		</div>

	</div>
</div>
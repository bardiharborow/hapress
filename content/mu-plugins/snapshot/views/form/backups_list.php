<form method="post">
	<?php Snapshot_Model_Request::nonce('snapshot-full_backups-list'); ?>
	<?php
		$table = new Snapshot_View_Table_FullBackups();
		$table->prepare_items($model->get_backups());

		$table->display();
	?>
</form>

<div id="restore-source" style="display:none">
	<?php Snapshot_View_Template::get('form')->load('restore'); ?>
</div>

<script>
;(function ($) {

var backup,
	restore_in_progress = false
;

function bind_restore_row_events (timestamp) {
	var $target = $("#restore_target");

	$target

		.find(".restore-steps .step .head")
			.off("click")
			.on("click", function (e) {
				if (e && e.preventDefault) e.preventDefault();
				if (e && e.stopPropagation) e.stopPropagation();

				var $me = $(this),
					$step = $me.closest(".step")
				;

				if (!restore_in_progress && $step.is(".progress")) return false; // Do not show progress until we're ready to

				$target.find(".step").removeClass("active");
				$step.addClass("active");

				return false;
			})
		.end()

		.find(".restore-steps .step .cancel")
			.off("click")
			.on("click", function (e) {
				e.preventDefault();
				e.stopPropagation();

				$target.closest('tr').remove();

				return false;
			})
		.end()

		.find(".restore-steps .step .check")
			.off("click")
			.on("click", function (e) {
				e.preventDefault();
				e.stopPropagation();

				var $me = $(this),
					$root = $target.find(".restore-steps .requirements-check"),
					$checking = $root.find(".checking"),
					$result = $root.find(".final")
				;
				$me.hide();
				$result.hide();
				$checking.show();

				$.post(ajaxurl, {
					action: "snapshot-full_backup-check_requirements"
				}).done(function (data) {
					$checking.hide();
					$result.show();
					var requirements_met = true;

					$.each(data, function (idx, parts) {
						var $me = $root.find("." + idx);
						$.each(parts, function (part, val) {
							var $part = $me.find("." + part),
								$res = $part.find(".result"),
								res_class = val.result ? 'pass' : 'fail'
							;
							$res
								.removeClass('pass').removeClass('fail')
								.addClass(res_class)
								.html(val.value)
							;
							if( ! val.result ) requirements_met = false;
						});
						var overall_class = $me.find(".fail").length ? 'fail' : 'pass';
						$me
							.removeClass('pass').removeClass('fail')
							.addClass(overall_class)
						;
					});

					var failure_show_class = $root.find(".fail").length ? 'warn' : '';
					$root.find(".failure-info")
						.removeClass('warn')
						.addClass(failure_show_class)
					;

					$result.find("section>header")
						.off("click")
						.on("click", function (e) {
							e.preventDefault();
							e.stopPropagation();

							var $parts = $(this).closest("section").find("article");
							if ($parts.is(":visible")) $parts.hide();
							else $parts.show();

							return false;
						})
					;

					$target.find(".restore-info-root .step.requirements .next")
						.off("click")
						.on("click", function (e) {
							e.preventDefault();
							e.stopPropagation();

							$target
								.find(".step").removeClass("active")
								.filter(".connect").addClass("active")
							;

							return false;
						})
						.show()
					;

					if( ! requirements_met ) {
						$target.find( ".restore-info-root .step.requirements .re-check" )
							.off( "click" )
							.on( "click", function ( e ) {
								e.preventDefault();
								e.stopPropagation();
								$( this ).hide();
								$target.find( ".restore-info-root .step.requirements .next" ).hide();
								$target.find( ".restore-info-root .step.requirements .check" ).trigger( "click" );

								return false;
							})
							.show()
						;
					}

				});

				return false;
			})
		.end()

		.find(".step.connect :hidden.archive").val(timestamp).end()

		.find(".restore-steps .step .run")
			.off("click")
			.on("click", function (e) {
				e.preventDefault();
				e.stopPropagation();

				var $step = $target.find(".step.progress"),
					$update = $step.find(".progress.update p")
				;

				$target.find(".step").removeClass("active");
				$step.addClass("active");

				restore($step).done(function () {
					$update.html($update.attr("data-done"));
				});

				return false;
			})
		.end()
	;

}

function show_restore_row ($row) {
	var numcols = $row.find('th,td').length,
		timestamp = $row.find('input[name="delete-bulk[]"]').val(),
		$src = $("#restore-source"),
		target = '<tr><td colspan=' + numcols + ' id="restore_target">' + $src.html() + '</td></tr>'
	;
	$("#restore_target").closest('tr').remove();
	$row.after(target);

	setTimeout(function () {
		bind_restore_row_events(timestamp)
	});

}

function report_error (msg) {
	console.log(msg);
	alert(msg);
}

function report_restore_error ($step, msg) {
	$step
		.find(".progress.update p").removeClass('current')
		.filter(".indicator").removeClass('current').end()
		.filter(".error").addClass('current').end()
	;
}

function restore ($step) {
	var prm = new $.Deferred(),
		$archive = $("#restore_target .step.connect :hidden.archive"),
		$restore = $("#restore_target .step.connect :text.location"),
		$creds = $("#restore_target .step.connect .request-filesystem-credentials-form input"),
		rq = {},
		callback = function (request) {
			request.action = "snapshot-full_backup-restore";
			$.post(ajaxurl, request, function () {}, 'json')
				.then(function (data) {
					if (!data || (data || {}).error) {
						report_restore_error($step, "Error restoring backup");
						prm.resolve();
						return false;
					}
					if (data.task !== 'clearing') {
						var cls = 'fetching' === data.task ? 'fetch' : 'process';
						restore_progress_display($step, cls);
						callback(request);
					} else {
						restore_progress_display($step, (data.status ? 'done' : 'error'));
						restore_in_progress = false;
						prm.resolve();
					}
				})
				.fail(function () {
					console.log(arguments);
					prm.resolve();
					return report_restore_error($step, "Restoration failed");
				})
			;
		}
	;

	rq = {
		idx: backup,
		archive: $archive.val(),
		restore: $restore.val(),
		credentials: {}
	};
	$creds.each(function () {
		var $me = $(this);
		if ($me.is(":radio") || $me.is(":checkbox")) {
			if ($me.is(":checked")) rq.credentials[$me.attr("name")] = $me.val();
		} else if ($me.is(":text") || $me.is(":password") || $me.is(":hidden")) {
			rq.credentials[$me.attr("name")] = $me.val();
		}
	});

	restore_in_progress = true;
	restore_progress_display($step, 'fetch');

	$(window).on("beforeunload.snapshot-restore", function (e) {
		var msg = "You still have a restore active, navigating off this page will stop it mid-process";
		e.returnValue = msg;
		return msg;
	});

	callback(rq);

	return prm.promise().always(function () {
		$(window).off("beforeunload.snapshot-restore");
	});
}

function restore_progress_display ($step, progress) {
	$step
		.find(".progress.update p").removeClass('current')
		.filter(".indicator").addClass('current').end()
		.filter("." + progress).addClass('current').end()
	;
	if ('done' === progress) $step.find("p.indicator").removeClass('current');
}

function download_backup ($row, attempt) {
	var timestamp = $row.find('input[name="delete-bulk[]"]').val();
	attempt = attempt || 0;

	$.post(ajaxurl, {
		action: "snapshot-full_backup-download",
		idx: timestamp
	}).done(function (data) {
		data = data || {};
		attempt += 1;
		if ('fetching' === data.task && attempt <= 10) return download_backup($row, attempt);
		if (!data.status || !data.nonce) return report_error("Error downloading backup");

		// Okay so we have the data. Fake our form
		var $frm = $("<form />")
			.attr('action', window.location.toString())
			.attr('method', 'post')
		;
		$frm.append(
			$('<input />')
				.attr("type", "hidden")
				.attr("name", "download")
				.attr("value", timestamp)
		);
		$frm.append(
			$('<input />')
				.attr("type", "hidden")
				.attr("name", "nonce")
				.attr("value", data.nonce)
		);
		$frm.appendTo('body').submit().remove();
	});
}

function delete_backup ($row) {
	var timestamp = $row.find('input[name="delete-bulk[]"]').val();

	$row
		.removeClass("delete-active")
		.addClass("delete-active")
	;

	$.post(ajaxurl, {
		action: "snapshot-full_backup-delete",
		idx: timestamp
	}).done(function (data) {
		data = data || {};
		if (!data.status) report_error("Error deleting backup");
		else window.location.reload();
	}).always(function () {
		$row.removeClass('delete-active');
	});
}

function init () {
	$('tr .actions .button, td.column-name.has-row-actions a[href="#restore"]').on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();

		show_restore_row($(this).closest('tr'));

		return false;
	});
	$('td.column-name.has-row-actions a[href="#trash"]').on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();

		delete_backup($(this).closest('tr'));

		return false;
	});
	$('td.column-name.has-row-actions a[href="#download"]').on("click", function (e) {
		e.preventDefault();
		e.stopPropagation();

		download_backup($(this).closest('tr'));

		return false;
	});
}

$(init);

})(jQuery);
</script>
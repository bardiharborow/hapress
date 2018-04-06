<div class="form-content">

    <div id="wps-destination-type" class="form-row">

        <div class="form-col-left">

            <label><?php _e( "Type", SNAPSHOT_I18N_DOMAIN ); ?></label>

        </div>

        <div class="form-col">

            <i class="wps-typecon local"></i>

            <label><?php _e( 'Local', SNAPSHOT_I18N_DOMAIN ); ?></label>

        </div>

    </div>

    <div id="wps-destination-name" class="form-row">

        <div class="form-col-left">

            <label><?php _e( "Name", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>

        </div>

        <div class="form-col upload-progress">

            <input type="text" class="inline"  name="snapshot-destination[name]" id="snapshot-destination-name" value="<?php if ( isset( $item['name'] ) ) { echo stripslashes( $item['name'] ); } ?>" disabled />

        </div>

    </div>

    <div id="wps-destination-dir" class="form-row">

        <div class="form-col-left">

            <label><?php _e( "Directory", SNAPSHOT_I18N_DOMAIN ); ?> <span class="required">*</span></label>

        </div>

        <div class="form-col">

            <input type="text" name="backupFolder" id="snapshot-destination-directory" value="<?php if ( isset( $item_object->backup_folder ) ) { echo stripslashes( $item_object->backup_folder ); } ?>"/>

        </div>

    </div>

    <input type="hidden" name="snapshot-destination[type]" id="snapshot-destination-type" value="<?php echo $item['type'] ?>"/>

</div>
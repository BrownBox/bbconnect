<?php
function bbconnect_report_quicklinks(array $user_ids, array $args = array()) {
    bbconnect_show_quicklinks('reports', $user_ids, $args);
}

function bbconnect_profile_quicklinks($user_id) {
    bbconnect_show_quicklinks('profile', array($user_id));
}

function bbconnect_show_quicklinks($location, array $user_ids, array $args = array()) {
?>
<div id="quicklinks-wrapper"><strong>QUICKLINKS</strong>
<?php
    $quicklinks = array();
    $quicklinks = apply_filters('bbconnect_quicklinks', $quicklinks, $location);
    $quicklinks = apply_filters('bbconnect_quicklinks_'.$location, $quicklinks);
    ksort($quicklinks);
    if (count($quicklinks) == 0) {
        echo '<p>No quicklinks found.</p>'."\n";
    } else {
        echo '    <ul>'."\n";
        foreach ($quicklinks as $quicklink) {
            $quicklink_name = get_class($quicklink);
            $quicklink->show_link($user_ids, $args);
        }
        echo '    </ul>'."\n";
    }
?>
</div>
<?php
}

function bbconnect_quicklinks_recursive_include($dir_name, $parent_dir = '', $starting_dir = '') {
    if (empty($starting_dir)) {
        $starting_dir = $dir_name;
    }
    $dir = opendir($dir_name);
    $files = array();
    $quicklinks = array();
    while (false !== ($filename = readdir($dir))) {
        if ($filename == '.' || $filename == '..') {
            continue;
        }

        if (is_dir($dir_name.$filename)) {
            bbconnect_quicklinks_recursive_include($dir_name.$filename.'/', $dir_name, $starting_dir);
        } elseif (strpos($filename, '.php') !== false) {
            include_once($dir_name.$filename);
            $quicklink_prefix = !empty($dir_name) ? str_replace('/', '_', str_replace($starting_dir, '', $dir_name)) : '';
            $quicklink_name = $quicklink_prefix.array_shift(explode('.', $filename)).'_quicklink';
            $quicklinks[$quicklink_name] = new $quicklink_name();
        }
    }
    add_filter('bbconnect_quicklinks_'.basename($dir_name), function($all_quicklinks) use ($quicklinks) {return array_merge($all_quicklinks, $quicklinks);});
}

function bbconnect_quicklinks_init() {
    $class_dir = dirname(__FILE__).'/quicklinks/';
    bbconnect_quicklinks_recursive_include($class_dir);
}

/**
 * Base class for quicklinks
 * @author markparnell
 */
abstract class bb_quicklink {
    var $title;
    var $link_template = '<li><a class="s-quicklinks button action %s" href="%s" %s>%s</a></li>';

    abstract public function show_link(array $user_ids, array $args = array());
    public function __construct() {

    }
}

/**
 * Base class for modal quicklinks
 * @author markparnell
 */
abstract class bb_modal_quicklink extends bb_quicklink {
    var $modal_id;
    var $modal_width = 600;
    var $modal_height = 550;

    abstract protected function modal_contents(array $user_ids = array(), array $args = array());

    public function __construct() {
        $this->modal_id = wp_generate_password(6, false);
    }

    /**
     * Outputs the quicklink button
     */
    public function show_link(array $user_ids, array $args = array()) {
        $url = '#TB_inline?width='.$this->modal_width.'&height='.$this->modal_height.'&inlineId='.$this->modal_id;
        printf($this->link_template, 'thickbox', $url, $attrs, $this->title);

        $this->output_modal($user_ids, $args);
    }

    /**
     * Outputs the thickbox modal and the required javascript
     */
    protected function output_modal(array $user_ids, array $args = array()) {
        add_thickbox(); // Make sure modal library is loaded
?>
<div id="<?php echo $this->modal_id; ?>" style="display: none;">
    <div style="overflow: scroll;">
        <h2><?php echo $this->title; ?></h2>
        <?php $this->modal_contents($user_ids, $args); ?>
    </div>
</div>
<?php
    }
}

/**
 * Base class for custom form quicklinks
 * @author markparnell
 * @since 2.5.6
 */
abstract class bb_form_quicklink extends bb_modal_quicklink {
    var $trigger_export = false;
    var $function_name = '';

    abstract protected function form_contents(array $user_ids = array(), array $args = array());
    abstract static public function post_submission();

    public function __construct() {
        parent::__construct();
        $this->function_name = get_class($this).'_action_submit';
        add_action('wp_ajax_'.get_class($this).'_submit', array(get_class($this), 'post_submission'));
    }

    /**
     * Outputs the quicklink button
     */
    public function show_link(array $user_ids, array $args = array()) {
        $url = '#TB_inline?width=600&height=550&inlineId='.$this->modal_id;
        printf($this->link_template, 'thickbox', $url, $attrs, $this->title);

        $this->output_modal($user_ids, $args);
    }

    protected function modal_contents(array $user_ids = array(), array $args = array()) {
?>
        <form action="" method="post">
            <?php $this->form_contents($user_ids, $args); ?>
            <br> <input type="submit" class="button action" onclick="jQuery(this).val('Processing, please wait...').prop('disabled', true); return <?php echo $this->function_name; ?>(this);" value="Submit">
        </form>
<?php
    }

    /**
     * Outputs the thickbox modal and the required javascript
     * @see self::form_contents()
     */
    protected function output_modal(array $user_ids, array $args = array()) {
        parent::output_modal($user_ids, $args);
?>
<script type="text/javascript">
    function <?php echo $this->function_name; ?>(e) {
        var tableName = '<?php echo $this->title; ?>';
        var data = {
                'action': '<?php echo get_class($this); ?>_submit',
                'user_ids': '<?php echo implode(',', $user_ids); ?>'
<?php
        foreach ($args as $key => $data) {
            if (is_array($data)) {
                $data = implode(',', $data);
            }
?>
                        ,'<?php echo $key; ?>': '<?php echo $data; ?>'
<?php
        }
?>
        };
        jQuery('#TB_ajaxContent form').find('textarea, input, select').each(function() {
            var element = jQuery(this);
            var fieldName = element.attr('name');
            if (typeof fieldName !== 'undefined') {
                if (element.hasClass('wp-editor-area') && tinymce.get(fieldName) !== null) {
                    data[fieldName] = tinymce.get(fieldName).getContent();
                } else if (element.attr('type') == 'checkbox' || element.attr('type') == 'radio') {
                    if (element.prop('checked')) {
                        data[fieldName] = element.val();
                    }
                } else {
                    data[fieldName] = element.val();
                }
            }
        });
        jQuery.post(ajaxurl, data, function(response) {
            if (response == 0) {
                var appendTableName = jQuery('#TB_ajaxContent form').find('select.append_table_name').first().children(':selected').text();
                if (appendTableName != '') {
                    tableName += ' - '+appendTableName;
                }
                jQuery(e).val('Submit').prop('disabled', false);
                tb_remove();
<?php
        if ($this->trigger_export) {
?>
                tableExport = jQuery('.wp-list-table').tableExport({
                        formats: ['csv'],
                        filename: tableName,
                        exportButtons: false
                });
                exportData = tableExport.getExportData()[jQuery('.wp-list-table').attr('id')]['csv'];
                tableExport.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
<?php
        } else {
?>
                window.location.reload();
<?php
        }
?>
            } else {
                alert(response);
                jQuery(e).val('Submit').prop('disabled', false);
            }
        });
        return false;
    }
</script>
<?php
    }

    /**
     * Add new history note to user(s)
     * @param string $title Note title
     * @param string $contents Note contents
     * @param integer $type Term ID of primary note type
     * @param integer $subtype Term ID of secondary note type
     * @param array $user_ids List of user IDs to add note to
     * @param boolean $action_required Whether the note should be marked as requiring action
     */
    public static function add_note($title, $contents, array $user_ids) {
        $action_form_id = bbconnect_get_action_form();

        foreach ($user_ids as $user_id) {
            $user = new WP_User($user_id);
            $_POST = array(); // Hack to allow multiple form submissions via API in single process
            $entry = array(
                    'input_18' => $user->user_email,
                    'input_1' => 'note',
                    'input_7' => $title,
                    'input_8' => $contents,
            );
            GFAPI::submit_form($action_form_id, $entry);
        }

        return true;
    }

    /**
     * Output select boxes for note type and sub-type
     */
    protected function output_note_type_selects() {
        $note_types = get_terms('bb_note_type', array('hide_empty' => false));
        $parent_types = $child_types = '<option value="" class="please_select">Please Select</option>';
        foreach ($note_types as $note_type) {
            $note_option = '<option value="'.$note_type->term_id.'" class="childof_'.$note_type->parent.'">'.$note_type->name.'</option>';
            if ($note_type->parent == 0) {
                $parent_types .= $note_option;
            } else {
                $child_types .= $note_option;
            }
        }
?>
<div class="modal-row">
    <label for="note_type">Note Type:</label><select id="note_type" name="note_type"><?php echo $parent_types ?></select>
</div>
<div class="modal-row">
    <label for="note_subtype">Note Sub-Type:</label><select id="note_subtype" name="note_subtype"><?php echo $child_types ?></select>
</div>
<script type="text/javascript">
            jQuery(document).ready(function() {
                filter_subtypes();
                jQuery('select[name="note_type"]').on('change', function() {
                    filter_subtypes();
                });
            });
            function filter_subtypes() {
                var e = jQuery('#TB_ajaxContent select[name="note_type"]');
                if (!e) {
                    e = jQuery('select[name="note_type"]');
                }
                jQuery('#note_subtype option:not(.please_select)').hide();
                jQuery('#note_subtype option.childof_'+e.find(':selected').val()).show();
            }
        </script>
<?php
    }
}

/**
 * Base class for external page quicklinks
 * @author markparnell
 */
abstract class bb_page_quicklink extends bb_quicklink {
    var $url;

    public function show_link(array $user_ids, array $args = array()) {
        printf($this->link_template, '', $this->url, ' target="_blank"', $this->title);
    }
}

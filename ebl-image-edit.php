<?php

if (@txpinterface == 'admin')
    {
        add_privs('eblimgeditext', '1'); 
        register_tab('extensions', 'eblimgeditext', 'EBL Image Edit');
        register_callback('ebl_image_edit_ext', 'eblimgeditext');

    switch (gps('event'))
        {
            case 'eblrotateleft':
                ebl_img_edit(gps('imgid'), 'left', 'rotate'); exit();
                break;
            case 'eblrotateright':
                ebl_img_edit(gps('imgid'), 'right', 'rotate'); exit();
                break;
            case 'eblrotateup':
                ebl_img_edit(gps('imgid'), 'up', 'rotate'); exit();
                break;
            case 'eblimgcrop':
                ebl_img_edit(gps('imgid'), '', 'crop'); exit();
                break;
            case 'eblimgcrop':
                ebl_processUpload(gps('uploadType'));
                break;
            case 'ebltmbcrop':
                ebl_img_edit(gps('imgid'), '', 'thumbnail'); exit();
                break;
            case 'eblrszimg':
                ebl_img_edit(gps('imgid'), '', 'rsz'); exit();
                break;
            case 'eblbackupimg':
                eblbackupimg(gps('imgid'));
                break;
            case 'eblrestoreimg':
                eblrestoreimg(gps('imgid'));
                break;
            case 'eblremoveimg':
                eblremoveimg(gps('imgid'));
                break;                
        }
    }

function ebl_image_edit_ext() 
{
    
    $step = gps('step');
    
    $message = (is_callable($step)) ? $step() : '';
    
    echo pagetop("EBL Image Edit Preferences", $message);
    
    echo    '<div style="margin: 0 auto; width: 400px;">'.n.
            ebl_list_cropStyles().
            '</div>';    
}

function ebl_list_cropStyles()
{
    global $txp_user, $path_to_site, $img_dir;
    
    $backupdir     = $path_to_site . '/' . $img_dir . '/backup/';
        if(!file_exists($backupdir)) {
            
        $createEBLtable = safe_query("CREATE TABLE `".PFX."ebl_crop` (".
                                    "`name` varchar(64) NOT NULL,".
                                    "`width` varchar(16) NOT NULL,".
                                    "`height` varchar(16) NOT NULL,".
                                    "`thumb` varchar(1) NOT NULL default '0',".
                                    "UNIQUE KEY `name` (`name`)".
                                    ");");
                                    
        $rs = safe_insert('ebl_crop', "`name` = 'Image Crop', `width` = '500',`height` = '500', `thumb` = '0'");
        $rs = safe_insert('ebl_crop', "`name` = 'Thumbnail Square', `width` = '100',`height` = '100', `thumb` = '1'");

            $mkdir = (@mkdir($backupdir)) ? TRUE : FALSE;
            if($mkdir) { 
                echo "<p  style=\"text-align: center; \">$backupdir did not exist previously. Now created.</p>";
            } else {
                echo "<p style=\"text-align: center; font-weight: bold; \">Unable to create $backupdir. Please check your folder permissions.</p>";
            }
        }


 
    echo n.n.hed(gTxt('Crop Styles'), 1, ' style="text-align: center; margin-top:2em; font-weight: bold;"').
        n.n.startTable('list').
        n.tr(
            n.hCell(gTxt('name')).
            n.hCell(gTxt('width')).
            n.hCell().
            n.hCell(gTxt('height')).
            n.hCell(gTxt('thumbnail')).
            n.hCell(gTxt('delete'))
        );

    $rs = safe_rows_start('*', 'ebl_crop', '1=1 ORDER BY `name`');

    $out = '';
    
    if ($rs)
    { 
        while ($a = nextRow($rs))
        { 
            $isChecked = ($a['thumb'] == 1) ? 'yes' : 'no';
            $out .= n.tr(
                    td( htmlspecialchars($a['name'])).
                    td( htmlspecialchars($a['width'])).
                    td( htmlspecialchars(' X ')).
                    td( htmlspecialchars($a['height'])).
                    td( $isChecked).
                    td( 
                        dLink('eblimgeditext', 'ebl_cropdelete', 'stylename', $a['name'])
                    )
                );
        }
    }
    
    echo form($out);
    
    echo n.tr(
        form(
            td( fInput('text', 'name', '', 'edit','','',10) ).
            td( fInput('text', 'width', '', 'edit','','',5) ).
            td().
            td( fInput('text', 'height', '', 'edit','','',5) ).
            td( '<input name="eblistmb" class="checkbox" type="checkbox">').
            td( fInput('submit', 'add', gTxt('add'), 'smallerbox') ).
            n.eInput('eblimgeditext').
            n.sInput('ebl_cropsavenew')
        )
    );

    echo n.endTable();
}

function selectStyles($thumb = '0') 
{

    $rs = safe_rows_start('*', 'ebl_crop', 'thumb='.$thumb.' ORDER BY `name`');

    $out = "<select name=\"customsize\" id=\"customsize$thumb\">' + ".n.
           "'<option value=\"Custom\">Custom</option>' + ".n;
    
    if ($rs)
    { 
        while ($a = nextRow($rs))
        {
            $width    = $a['width'];
            $height    = $a['height'];
            $name     = $a['name'];

            $out .= "'<option value=".$width."x".$height."\>".htmlspecialchars($name)." : $width x $height </option>' + ".n;
        }
    }
    $out .= "'</select>";
    
    return $out;
}

function ebl_cropsavenew()
{

    extract(doSlash(psa(array('name', 'width','height','eblistmb'))));
    
    if ($name && is_numeric($width) && is_numeric($height))
    { 
        $eblistmb = ($eblistmb == "on") ? 1 : 0;
        
        $rs = @safe_insert('ebl_crop', "
            `name`     = '$name',
            `width`     = '$width',
            `height` = '$height',
            `thumb`  = '$eblistmb'");

        return ($rs) ?
            "New crop style created" : 
            "<b>Error:</b> Duplicate Name";    
        
    } else {
        
        return "<b>Width</b> and <b>Height</b> must be numeric values";
    }
    
    return FALSE;
}

function ebl_cropdelete()
{
    $name = ps('stylename');
    return (safe_delete('ebl_crop', "`name` = '".doSlash($name)."'")) ? 
        "<b>Deleted</b> $name" : 
        "<b>Error</b> Unable to delete $name"; 
}

if(gps('step') == 'image_edit' || gps('step') == 'thumbnail_insert' || gps('step') == 'image_replace' || gps('step') == 'thumbnail_create' || gps('step') == 'thumbnail_delete' || gps('step') == 'image_insert') {
    register_callback('ebl_imgcrop', 'admin_side', 'head_end');
}

function ebl_imgcrop() 
{

    global $path_to_site,$img_dir;
                    
    @$id = (gps('id') > 0) ? gps('id') : $GLOBALS['ID'];
    
    if(!is_numeric($id)) return;
    
    $imagedir = hu . $img_dir . '/';
    
    $rs = @safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

    if ($rs) {
        extract($rs);
        $filename = $id.$ext;
        $imgsrc = $imagedir.$filename;
        $tmbsrc = $imagedir.$id.'t'.$ext;
    }
    
    $customThumbSize = selectStyles(1);
    $customImgSize = selectStyles(0);
    
    echo <<< EOF
<script src="jcrop/jquery.Jcrop.pack.js"></script>
<link href="jcrop/jquery.Jcrop.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#eblimgprocess {
    margin: 5px;
    font-weight: bold; 
    font-size: 14pt;
    color: #550000;
    padding: 0 0 0 20px;
    background: url('jcrop/processing.gif') left no-repeat;
}
a { padding: 0 0 3px;}
a:visited {} a:hover {} a:active { border: 0; }
a:focus { outline: 0; text-decoration: none;} 
</style>
<script type="text/javascript">
$(document).ready(function() { // init everything

    $("#list td:first img").attr('id','mainImg'); // Give our image an ID
    $("#list td:first").attr('id','mainImgTD'); // name the table to make it easier to find.

    $('#image-thumbnail td:first img').attr('id','thumb'); // Give our thumbnail image an ID
    $('#image-thumbnail td:first').attr('id','thumbTD'); // name the TD that contains the image so we can swap it out.

    if(! $('#thumbTD').length ) {  $("#list td:eq(1)").attr('id','thumbTD');  }

    $('#mainImgTD').prepend('<div id="eblcropui"></div>');
    $('#eblcropui').append('<fieldset style="min-width: 500px;"><legend>Edit Image</legend><div id="eblcontainer"></div></fieldset>');
    
    $('#eblcontainer').css({'margin':'10px'});
    $('#eblcontainer').append(
                        '<p>' + 
                         '<a href="#" id="eblcroplnk" >Crop</a> | ' + 
                         '<a href="#" id="ebltmblnk" >Thumbnail</a> | ' + 
                         '<a href="#" id="eblrotatelnk" >Rotate</a> | ' +
                         '<a href="#" id="eblresizelnk" >Resize</a> | ' + 
                         '<a href="#" id="eblbackuplnk" >Backup</a>' + 
                        '</p>'
                        );
    
    $('#eblcontainer').append('<div id="eblcropdata"></div><div id="eblcropctrl"></div><div id="ebltmbctrl"></div><div id="eblrotatectrl"></div><div id="eblresize"></div><div id="eblbackupimg"></div><div id="eblhiddendata"></div><div id="eblimgprocess"> Processing...</div>');
    
    $('#eblcropctrl').append(
                                 '<input type="hidden" id="eblcropHh" name="eblcropH" value="" />' +
                                 '<input type="hidden" id="eblcropWh" name="eblcropW" value="" />' +
                                 '<input type="hidden" id="eblcropXh" name="eblcropXh" value="" />' +
                                 '<input type="hidden" id="eblcropYh" name="eblcropYh" value="" />'
                            );
    
    $('#eblcropctrl').append(
                                '<p>Use predefined size: $customImgSize | <input name="aspectratio" type="checkbox" id="imgaspectratio" value="" /> Lock Aspect Ratio </p>' +
                                '<input type="submit" name="$id" id="eblimgcrop" value="Crop Original" class="smallerbox" />'
                            );
    
    $('#ebltmbctrl').append(                             
                                '</p><p>Use predefined size: $customThumbSize | ' + 
                                '<input name="aspectratio" type="checkbox" id="tmbaspectratio" value="" /> Lock Aspect Ratio </p></p><p> Thumbnail Dimensions: ' + 
                                 '( W: <input name="ebltmbW" type="text" id="ebltmbW" size="5" maxlength="4" value="100" /> ) x ' +
                                 '( H: <input name="ebltmbH" type="text" id="ebltmbH" size="5" maxlength="4" value="100" /> )' + 
                                 '| <input name="cropthumb" type="checkbox" id="cropthumb" checked="true"/> Resize Thumbnail ' + 
                                '</p><p>' +  
                                 '<input type="button" name="$id" id="ebltmbcrop" value="Create Thumbnail" class="smallerbox" />' + 
                                '</p>'
                            );
    
    $('#eblrotatectrl').append(
                                '<p>Rotate <input type="button" name="$id" id="rotateright" value="Clockwise 90&deg;" class="smallerbox" /> ' +
                                 '<input type="button" name="$id" id="rotateleft" value="Counterclockwise 90&deg; " class="smallerbox" /> ' +
                                 '<input type="button" name="$id" id="rotateup" value="180 &deg; " class="smallerbox" />' + 
                                '</p>'
                            );
                            
    $('#eblbackupimg').append(
                                '<p><input type="button" name="$id" id="eblbackup" value="Backup" class="smallerbox" /> ' +
                                '<input type="button" name="$id" id="eblrestore" value="Restore" class="smallerbox" /> ' + 
                                '<input type="button" name="$id" id="ebldeleteBackup" value="Remove Backup" class="smallerbox" />' + 
                                '</p>'
                            );
    
    $('#eblresize').append(
                                '<p>Current Image Size : W[ <span id="origW"></span> ] x H[ <span id="origH"></span> ]</p>' + 
                                '<p><label for="eblrszW">Width: </label><input type="text" name="eblrszW" id="eblrszW" /> x ' +
                                 '<label for="eblrszH">Height: </label><input type="text" name="eblrszH" id="eblrszH" /> ' +
                                  '<input type="button" name="$id" id="eblrszimg" value="Resize" class="smallerbox" />' +
                                '</p>'
                            );
    
    $('#eblcropdata').append(
                                '<p>Current Crop Area (W: <span style="font-weight: bold;" id="eblcropW">0</span>) x (H: <span style="font-weight: bold;" id="eblcropH">0</span>)</p>'
                            );
    
    $('#eblcropctrl,#eblrotatectrl,#eblresize,#ebltmbctrl,#eblcropdata,#eblimgprocess,#eblbackupimg').hide();
    
    
    // set our defaults
    window.crop = false;
    window.tmbcrop = false;

    $('#mainImg').wrap('<div id="imgDiv"></div');
    $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);

    
function removeCrop() {
    // Removes jCrop if found.
    if ( $('.jcrop-holder').length ) {
        var x = $('#imgDiv');
        
        // generate random number to append to img url to avoid cache issues
        var rand = Math.random();
        
        // hide Width/Height data
        $('#eblcropdata').hide(); 
        
        // replace removed content with just the image.
        x.empty().append('<img src="$imgsrc?' + rand +'" id="mainImg" />');
        window.myCrop = '';
    }
    
    window.cropOn = false;
    window.crop = false;
    window.tmbcrop = false;
}

function addCrop() {
    // Bind jCrop to #mainImg
    var aspectratio = $('#aspectratio').attr('checked') ? 1 : 0; // Thanks Manfre

    // Display H X W
    $('#eblcropdata').show(); 

    // check if the jcrop holder is already active. Load if not active.
    if (! $('.jcrop-holder').length ) {
        window.myCrop = $.Jcrop('#mainImg',{ 
            onSelect: showCoords,
            onChange: showCoords,
            aspectRatio: aspectratio
        });
    myCrop.setSelect([ 0, 0, 100, 150 ]);
    }
    window.cropOn = true;
}
    
    
    $('#eblcroplnk').click(function () {
        $(this).css({'font-weight':'bold'});
        $('#eblresizelnk,#eblrotatelnk,#ebltmblnk,#eblbackuplnk').css('font-weight','normal');
        $('#eblrotatectrl,#ebltmbctrl,#eblresize,#eblrotatectrl,#eblbackupimg').hide();
        $('#eblcropctrl').toggle();

    
        if(window.crop && window.cropOn) { // user closed main crop function
            window.crop = false;
            removeCrop();  // remove the crop binding
        } else if (!window.crop && window.cropOn) { // user switched from thumb crop
            window.tmbcrop = false;
            window.crop = true;
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);
            $('#customsize0').val('Custom');
            myCrop.setOptions({aspectRatio: 0});
            myCrop.animateTo([ 0, 0, 100, 150 ]);
        } else { // user opened main crop function
            addCrop(); // add crop binding
            window.crop = true;
        }
    });

    $('#ebltmblnk').click(function () {
        $(this).css('font-weight', 'bold');
        $('#eblresizelnk,#eblrotatelnk,#eblcroplnk,#eblbackuplnk').css('font-weight','normal');
        $('#ebltmbctrl').toggle();
        $('#eblrotatectrl,#eblcropctrl,#eblresize,#eblrotatectrl,#eblbackupimg').hide();
    
        if(window.tmbcrop && window.cropOn) { // user closed thumb crop
            window.tmbcrop = false; 
            removeCrop();
        } else if (!window.tmbcrop && window.cropOn) { // user switched from main crop
            window.tmbcrop = true;
            window.crop = false;
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);
            $('#customsize1').val('Custom');
            myCrop.setOptions({aspectRatio: 0});
            myCrop.animateTo([ 0, 0, 100, 150 ]);
        } else { // user opens thumb crop
            addCrop();
            window.tmbcrop = true;
        }
    
    });

    $('#eblrotatelnk').click(function () {
        $(this).css('font-weight', 'bold');
        $('#eblresizelnk,#ebltmblnk,#eblcroplnk,#eblbackuplnk').css('font-weight','normal');
        $('#eblcropctrl,#ebltmbctrl,#eblresize,#eblcropdata,#eblbackupimg').hide();
        $('#eblrotatectrl').toggle();

        removeCrop();
        window.myCrop = ' ';
        window.crop = false;
    });

    $('#eblresizelnk').click(function () {
        $(this).css('font-weight', 'bold');
        $('#eblrotatelnk,#ebltmblnk,#eblcroplnk,#eblbackuplnk').css('font-weight','normal');
        $('#eblrotatectrl,#eblcropctrl,#ebltmbctrl,#eblrotatectrl,#eblcropdata,#eblbackupimg').hide();
        $('#eblresize').toggle();
        
        var imgW = $('#mainImg').width();
        var imgH = $('#mainImg').height();
        
        $('#origW').text(imgW);
        $('#origH').text(imgH);
        
        removeCrop();    
    });

    $('#rotateleft').click(function () {
        removeCrop();
        var id = $('#rotateright').attr('name');

        var rand = Math.random();
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrotateleft&imgid="+id,
            success: function(html){
                if(html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand +'" id="mainImg" />');                    
                } else {
                    alert(html);
                }
            }
        });
    });

    $('#rotateright').click(function () {
        removeCrop();
        var id = $('#rotateright').attr('name');
        var rand = Math.random();
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrotateright&imgid="+id,
            success: function(html){
                if(html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand +'" id="mainImg" />');
                } else {
                    alert(html);
                }
            }
        });
    });

    $('#rotateup').click(function () {
        removeCrop();
        var id = $('#rotateright').attr('name');
        var rand = Math.random();
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrotateup&imgid="+id,
            success: function(html){
                if(html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand +'" id="mainImg" />');
                } else {
                    alert(html);
                }
            }
        });
    });

    $('#eblimgcrop').click(function () {
        var id = $('#eblimgcrop').attr('name');
        var H = $('#eblcropHh').attr('value');
        var W = $('#eblcropWh').attr('value');
        var X = $('#eblcropXh').attr('value');
        var Y = $('#eblcropYh').attr('value');
        $('#eblimgprocess').show();
        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblimgcrop&imgid="+id,
            data:     "&eblcropXh=" + X + "&eblcropYh=" + Y + "&eblcropW=" + W + "&eblcropH=" + H,
            success: function(html){
                $('#eblimgprocess').hide();
                if(html.match(/success/)) {
                    removeCrop();
                    $('#eblcropctrl').toggle();
                } else {
                    alert(html);
                }
            }
        });
    });
    
    $('#ebltmbcrop').click(function () {
        var id = $('#eblimgcrop').attr('name');
        var H = $('#eblcropHh').attr('value');
        var W = $('#eblcropWh').attr('value');
        var X = $('#eblcropXh').attr('value');
        var Y = $('#eblcropYh').attr('value');
        
        var tH = $('#ebltmbH').attr('value');
        var tW = $('#ebltmbW').attr('value');

        $('#eblimgprocess').show();
        var rszTmb = $('#cropthumb').attr('checked') ? 'rsz' : 'no';
            
        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=ebltmbcrop&imgid="+id,
            data: "&rszTmb=" + rszTmb + "&eblcropXh=" + X + "&eblcropYh=" + Y + "&eblcropW=" + W + "&eblcropH=" + H + "&ebltmbH=" + tH + "&ebltmbW=" + tW,
            success: function(html){
                $('#eblimgprocess').hide();
                if(html.match(/success/)) {
                    removeCrop();
                    $('#thumbTD').empty().append('<img src="$tmbsrc?' + rand +'" id="thumb" />');
                    $('#ebltmbctrl').toggle();
                } else {
                    alert(html);
                }
            }
        });
    });
    
    $('#eblrszimg').click(function () {
        var id = $('#eblrszimg').attr('name');
        var H = $('#eblrszH').attr('value');
        var W = $('#eblrszW').attr('value');
    
        $('#eblimgprocess').show();
            
        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrszimg&imgid="+id,
            data: "&eblrszH=" + H + "&eblrszW=" + W,
            success: function(html){
                $('#eblimgprocess').hide();
                if(html.match(/success/)) {
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand +'" id="mainImg" />');
                    // 
                } else {
                    alert(html);
                }
            }
        });
    });


    $('#eblbackuplnk').click(function () {
        $(this).css('font-weight', 'bold');
        $('#eblrotatelnk, #eblresizelnk,#ebltmblnk,#eblcroplnk').css('font-weight','normal');
        $('#eblrotatectrl,#eblcropctrl,#ebltmbctrl,#eblresize,#eblrotatectrl,#eblcropdata').hide();
        $('#eblbackupimg').toggle();
    });
    
    $('#eblbackup').click(function () {
        var id = $(this).attr('name');
        $('#eblimgprocess').show();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblbackupimg&imgid="+id,
            success: function(html){
                if(html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    alert("image backed up");
                } else {
                    $('#eblimgprocess').hide();
                    alert("Error : " + id + "  |   " + html);
                }
            }
        });
    });
    
    $('#eblrestore').click(function () {
        var id = $(this).attr('name');
        $('#eblimgprocess').show();
        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblrestoreimg&imgid="+id,
            success: function(html){
                if(html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    alert("image restored");
                    var x = $('#imgDiv');
                    x.empty().append('<img src="$imgsrc?' + rand +'" id="mainImg" />');
                } else {
                    $('#eblimgprocess').hide();
                    alert("Error : " + html);
                }
            }
        });
    });

    $('#ebldeleteBackup').click(function () {
        var id = $(this).attr('name');
        $('#eblimgprocess').show();
        var rand = Math.random();
        $.ajax({
            type: "POST",
            url: "index.php?event=eblremoveimg&imgid="+id,
            success: function(html){
                if(html.match(/success/)) {
                    $('#eblimgprocess').hide();
                    alert("Backup image removed");
                } else {
                    $('#eblimgprocess').hide();
                    alert("Error : " + html);
                }
            }
        });
    });
    
    $('#customsize0,#customsize1').change(function () {
        var size = $(this).val();
        if(!size.match("Custom")) {
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', false).attr('checked', true);
            var x = size.split('x')[0];
            var y = size.split('x')[1];

            myCrop.animateTo([ 0, 0, x, y ]);
            
            var aspectratio = x / y;

            var opt = {
                aspectRatio: aspectratio
            }
            
            myCrop.setOptions(opt);
        } else {
            $('#imgaspectratio,#tmbaspectratio').attr('disabled', true).attr('checked', false);
            myCrop.setOptions({aspectRatio: 0});
        }
        
    });

    $('#imgaspectratio,#tmbaspectratio').change(function () {
        if($(this).attr('checked'))
        {
            if(this.id === 'imgaspectratio') 
            {
                var size = $('#customsize0').val();
            } else {
                var size = $('#customsize1').val();
            }
            
            var x = size.split('x')[0];
            var y = size.split('x')[1];    
            
            var aspectratio = x / y;
            myCrop.animateTo([ 0, 0, x, y ]);

        } else {
            var aspectratio = 0;
            
        }

        var opt = {
            aspectRatio: aspectratio
        }
        myCrop.setOptions(opt);
    });
    
    $('#cropthumb').change(function () {
            var cropthumb = $(this).attr('checked') ? false : true;
            
            if(cropthumb) {
                $('#ebltmbW, #ebltmbH').attr({disabled:"disabled",value:"###"});
            } else {
                $('#ebltmbW, #ebltmbH').removeAttr("disabled");
                $('#ebltmbW, #ebltmbH').attr("value","100");
            }
    });
});

function showCoords(c) {
        $('#eblcropX').text(c.x);
        $('#eblcropXh').val(c.x);
        $('#eblcropY').text(c.y);
        $('#eblcropYh').val(c.y);
        $('#eblcropX2').text(c.x2);
        $('#eblcropX2h').val(c.x2);
        $('#eblcropY2').text(c.y2);
        $('#eblcropY2h').val(c.y2);
        
        var imgW = zeroPad(c.w,4);
        var imgH = zeroPad(c.h,4);
        
        $('#eblcropW').text(imgW);
        $('#eblcropWh').val(c.w);
        $('#eblcropH').text(imgH);
        $('#eblcropHh').val(c.h);
};

function zeroPad(num,count) {
    var numZeropad = num + '';
    while(numZeropad.length < count) {
        numZeropad = "0" + numZeropad;
    }
    return numZeropad;
}

</script>
EOF;

}

function eblrestoreimg($id) 
{
    global $path_to_site,$img_dir;
    
    $rs = safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");
        
    if ($rs) {
        $backupdir     = $path_to_site . '/' . $img_dir . '/backup/';
        $imagedir    = $path_to_site . '/' . $img_dir . '/';
        
        extract($rs);
        $filename = $id.$ext;
        $imgsrc = $imagedir.$filename;        
        
        if(@copy($backupdir.$filename, $imagedir.$filename)) 
        {
            list($width, $height) = getimagesize($backupdir.$filename);
            if(safe_update('txp_image', "w = '".$width."', h = '".$height."'", "id = $id")) 
            {
                echo "success"; exit();
            } else {
                echo "File Copied but db not updated";
            }
        } else {
            echo "Backup does not exist";
        }
    } else {
        echo "Database Error";
    }

    exit();
}

function eblbackupimg($id) 
{    
    global $path_to_site,$img_dir;
    
    $rs = safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

    if ($rs) {
        $backupdir     = $path_to_site . '/' . $img_dir . '/backup/';
        $imagedir    = $path_to_site . '/' . $img_dir . '/';
        
        extract($rs);
        $filename = $id.$ext;
        $imgsrc = $imagedir.$filename; echo $imagedir.$filename;
    
        $cr = copy($imagedir.$filename,$backupdir.$filename);
        echo "success";
    } else {
        echo "Database Error";
    }
    
    exit();
}

function eblremoveimg($id)
{
    global $path_to_site,$img_dir;

    $rs = safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

    if ($rs) {
        $backupdir     = $path_to_site . '/' . $img_dir . '/backup/';
        $imagedir    = $path_to_site . '/' . $img_dir . '/';
        
        extract($rs);
        $filename = $id.$ext;
        $imgsrc = $imagedir.$filename; echo $imagedir.$filename;

        $cr = unlink($backupdir.$filename);
        echo "success";
    } else {
        echo "Database Error";
    }

    exit();        
}

function ebl_img_edit ($id, $direction, $action)
{
    global $path_to_site,$img_dir;

    $imagedir = $path_to_site . '/' . $img_dir . '/';

    $rs = safe_row("*, unix_timestamp(date) as uDate", "txp_image", "id = $id");

    if ($rs) {
        extract($rs);
        $filename = $id.$ext;

        switch (strtolower($ext)) {
            case '.jpg':
                $srcimage = imagecreatefromjpeg($imagedir . $filename);
                break;
            case '.gif':
                $srcimage = imagecreatefromgif($imagedir . $filename);
                break;
            case '.png':
                $srcimage = imagecreatefrompng($imagedir . $filename);
                break;
        }

        if($action == 'rotate') { // rotate image

            /** Ternary equivalent of  if / elseif / else : Done for simple shorthand. Assumes anything not right / left = 180 **/
            $degrees = ($direction == 'right') ? -90 : (($direction == 'left') ? 90 : 180);
            $newimg  = imagerotate($srcimage, $degrees, 0);
        } elseif ($action == 'crop') { // primary image cropping

            //gather crop variables from hidden fields.
            $cropX = gps('eblcropXh');
            $cropY = gps('eblcropYh');
            $targW = $cropW = gps('eblcropW');
            $targH = $cropH = gps('eblcropH');
            
            // prevents division by zero & invalid crop widths.
            $value = (int)$targH + (int)$targW;
            if($value < 30) {
                echo "invalid crop values detected";
                imagedestroy($srcimage);
                exit();
            }

            /** Create base canvas **/
            $newimg = imagecreatetruecolor($targW,$targH);

            /** Use the X/Y coords to plot the initial point, width/height take care of the rest **/
            imagecopyresampled($newimg, $srcimage,0,0, $cropX, $cropY, $targW, $targH, $cropW, $cropH);

        } elseif ($action == 'thumbnail') { // create thumbnail

            //gather crop variables from hidden fields.
            $cropX = gps('eblcropXh');
            $cropY = gps('eblcropYh');
            $cropH = gps('eblcropH');
            $cropW = gps('eblcropW');
            
            // prevents division by zero & invalid crop widths.
            $value = (int)$cropH + (int)$cropW;
            if($value < 4) {
                echo "invalid crop values detected";
                imagedestroy($srcimage);
                exit();
            }
            
            if(gps('rszTmb') == "rsz") { // are we resizing the the thumbnail?
                $targH = gps('ebltmbH');
                $targW = gps('ebltmbW'); 
            
            // did someone forget to input a height or width? If so, default to crop width
            if($targH < 1) { $targH = $cropH; }
            if($targW < 1) { $targW = $cropW; }

                // determine the correct scale value
                $scale = min($targW / $cropW,$targH / $cropH);
                // are the sides equal? If not, scale to fit.
                if ($scale < 1) {
                    $targW = ceil($scale * $cropW);
                    $targH = ceil($scale * $cropH);
                } 
            } elseif (gps('rszTmb') != 'rsz') { // if not, we'll just use the crop-area size to create our thumbnail
                $targH = $cropH;
                $targW = $cropW;
            }
            
            /** Create base canvas **/
            $newimg = imagecreatetruecolor($targW,$targH);
            /** Use the X/Y coords to plot the initial point, width/height take care of the rest **/
            imagecopyresampled($newimg, $srcimage,0,0, $cropX, $cropY, $targW, $targH, $cropW, $cropH);

        } elseif ($action == 'rsz') { // general resize
            $targH = gps('eblrszH');
            $targW = gps('eblrszW');
            
            if(($targH + $targW) < 1) {
                echo " ERROR : Dimensions must be entered.";
                return false;
            }
            // did someone forget to input a height or width? If so, we'll default to the original size for the missing value.
            if($targH < 1) { $targH = $h; }
            if($targW < 1) { $targW = $w; }
            // checks to see if the targeted values are larger than the original, if so, reset to original value as the longest side.
            if($targH > $h) { $targH = $h; }
            if($targW > $w) { $targW = $w; }
            
            $scale = min($targW / $w, $targH / $h);
            // are the sides equal? If not, scale to fit.
            if ($scale < 1) {
                $targW = ceil($scale * $w);
                $targH = ceil($scale * $h);
            } else { // image is smaller than requested scale. Abort.
                imagedestroy($srcimage);
                echo "Image smaller than parameters entered";
                return;
            }

            /** Create base canvas **/
            $newimg = imagecreatetruecolor($targW,$targH);
            /** Use the X/Y coords to plot the initial point, width/height take care of the rest **/
            imagecopyresampled($newimg, $srcimage,0,0,0,0, $targW, $targH, $w, $h);
        }

        $t = ($action == 'thumbnail') ? 't' : '';
        
        $filename = $id.$t.$ext;
        
        switch (strtolower($ext)) {
            case ($ext == '.jpg' || $ext == '.jpeg'):
                $fileresult = imagejpeg($newimg,$imagedir . $filename,'100');
                break;
            case ".gif":
                $fileresult = imagegif($newimg,$imagedir . $filename);
                break;
            case ".png":
                $fileresult = imagepng($newimg,$imagedir . $filename);
                break;
        }

        imagedestroy($srcimage);
    }

    if($action != 'thumbnail') 
    {
        list ($width,$height) = getimagesize($imagedir . $filename);
        $rs = safe_update('txp_image', "w = '".$width."', h = '".$height."'", "id = $id");
    } else {
        $rs = safe_update('txp_image', "thumbnail = '1'", "id = $id");
    }
    
    echo ($fileresult && $rs) ? "success" : 'ERROR ';
}

?>
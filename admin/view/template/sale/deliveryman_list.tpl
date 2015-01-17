<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
      <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/customer.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
          <!--<a onclick="$('form').attr('action', '<?php echo $approve; ?>'); $('form').submit();" class="button"><?php echo $button_approve; ?></a>-->
          <a href="<?php echo $insert; ?>" class="button"><?php echo $button_insert; ?></a>
          <a onclick="$('form').attr('action', '<?php echo $delete; ?>'); $('form').submit();" class="button"><?php echo $button_delete; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
              <td class="left" style="width: 25%;">配送员工号</td>
              <td class="left" style="width: 25%;">姓名</td>
              <td class="left" style="width: 25%;">电话</td>
              <td class="right" style="width: 25%;"><?php echo $column_action; ?></td>
            </tr>
          </thead>
          <tbody>
            <tr class="filter">
              <td></td>
              <td><input type="text" name="filter_no" value="<?php echo $filter_no; ?>" /></td>
              <td><input type="text" name="filter_name" value="<?php echo $filter_name; ?>" /></td>
              <td><input type="text" name="filter_telephone" value="<?php echo $filter_telephone; ?>" /></td>
              <td align="right"><a onclick="filter();" class="button"><?php echo $button_filter; ?></a></td>
            </tr>
            <?php if ($deliverymans) { ?>
            <?php foreach ($deliverymans as $deliveryman) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($deliveryman['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $deliveryman['deliveryman_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $deliveryman['deliveryman_id']; ?>" />
                <?php } ?></td>
              <td class="left"><?php echo $deliveryman['deliveryman_no']; ?></td>
              <td class="left"><?php echo $deliveryman['name']; ?></td>
              <td class="left"><?php echo $deliveryman['telephone']; ?></td>
              <td class="right"><?php foreach ($deliveryman['action'] as $action) { ?>
                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                <?php } ?></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="5"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--

    function filter() {

        var url = makeFilterURL('index.php?route=sale/deliveryman&token=<?php echo $token; ?>');

        window.location = url;
    }
    
//--></script> 

<?php echo $footer; ?>
<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
      <h1><?php echo $heading_title; ?></h1>
      <p class="lead"><?php echo $sub_title; ?></p>
      <?php echo $top; ?>
      <?php foreach ($sections as $section) { ?>
      <?php if ($section['title']) { ?>
      <<?php echo $section['tag']; ?>><?php echo $section['title']; ?></<?php echo $section['tag']; ?>>
      <?php } ?>
      <div class="row">
        <?php foreach ($section['groups'] as $group) { ?>
        <div class="col-sm-4">
          <?php if ($group['title']) { ?>
          <<?php echo $group['tag']; ?>><?php echo $group['title']; ?></<?php echo $group['tag']; ?>>
          <?php } ?>
          <ul class="list-unstyled">
            <?php foreach ($group['links'] as $link) { ?>
            <li><a href="<?php echo $link['href']; ?>"><i class="fa fa-file-excel-o"></i> <?php echo $link['name']; ?></a></li>
            <?php } ?>
          </ul>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
      <?php if ($sections) { ?>
      <form id="price-offer-send" action="<?php echo $send; ?>" method="post" class="form-horizontal">
        <fieldset>
          <legend><?php echo $text_send; ?></legend>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-offer"><?php echo $entry_offer; ?></label>
            <div class="col-sm-10">
              <select name="offer" id="input-offer" class="form-control">
                <option value=""><?php echo $text_choose; ?></option>
                <?php foreach ($sections as $section) { ?>
                <?php foreach ($section['groups'] as $group) { ?>
                <?php foreach ($group['links'] as $link) { ?>
                <option value="<?php echo $link['key']; ?>"><?php echo $link['title']; ?></option>
                <?php } ?>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-email"><?php echo $entry_email; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input type="email" name="email" id="input-email" class="form-control">
                <span class="input-group-btn"><button type="submit" class="btn btn-primary"><?php echo $button_send; ?></button></span>
              </div>
            </div>
          </div>
        </fieldset>
      </form>
      <?php } ?>
      <?php echo $bottom; ?>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<script type="text/javascript"><!--
$('#price-offer-send').on('submit', function(e) {
	e.preventDefault();

	var form = $(this);

	$.ajax({
		url: form.attr('action'),
		type: 'post',
		dataType: 'json',
		data: form.serialize(),
		beforeSend: function() {
			form.find('button').button('loading');
		},
		complete: function() {
			form.find('button').button('reset');
		},
		success: function(json) {
			form.find('.alert').remove();

			if (json['error']) {
				form.prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</div>');
			}

			if (json['success']) {
				form.prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + '</div>');
				form.find('input[name=\'email\']').val('');
			}
		}
	});
});
//--></script>
<?php echo $footer; ?>

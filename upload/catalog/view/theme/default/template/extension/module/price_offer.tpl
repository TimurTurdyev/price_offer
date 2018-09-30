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
      <?php echo $bottom; ?>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>

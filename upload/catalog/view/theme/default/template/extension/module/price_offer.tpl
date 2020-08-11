<?php echo $header; ?>
    <!-- Page Title (Shop)-->
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-star">
                        <?php $b_count = count($breadcrumbs) - 1;
                        foreach ($breadcrumbs as $key => $breadcrumb) { ?>
                            <?php if ($key == 0) { ?>
                                <li class="breadcrumb-item">
                                    <a class="text-nowrap" href="<?php echo $breadcrumb['href']; ?>">
                                        <i class="czi-home"></i><?php echo $breadcrumb['text']; ?></a>
                                </li>
                            <?php } else {
                                if ($key < $b_count) { ?>
                                    <li class="breadcrumb-item text-nowrap">
                                        <a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
                                    </li>
                                <?php } else { ?>
                                    <li class="breadcrumb-item text-nowrap active"
                                        aria-current="page"><?php echo $breadcrumb['text']; ?></li>
                                <?php }
                            } ?>
                        <?php } ?>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pr-lg-4 text-center text-lg-left">
                <h1 class="h3 text-light mb-0"><?php echo $heading_title; ?></h1>
            </div>
        </div>
    </div>
    <section class="container mt-md-2 pt-2 pt-md-4 pb-5">
        <h2 class="text-center mb-5"><?php echo $heading_sub_title; ?></h2>
        <?php if (!empty($text['mini_description'])) {
            echo html_entity_decode($text['mini_description'], ENT_QUOTES, 'UTF-8');
        } ?>
        <?php foreach ($settings as $table_index => $group) { ?>
            <div class="card border-0">
                <div class="card-body">
                    <?php if ($group['group_name']) { ?>
                        <?php if (!empty($group['group_tag'])) { ?>
                            <?php echo ('<' . $group['group_tag'] . ' class="h4">' . $group['group_name'] . '</' . $group['group_tag'] . '>'); ?>
                        <?php } else { ?>
                            <h2 class="h5"><?php echo $group['group_name']; ?></h2>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($group['groups']) { ?>
                        <div class="row">
                            <?php foreach ($group['groups'] as $group_index => $group_links) { ?>
                                <?php if ($group_links['name'] || isset($group_links['items'])) { ?>
                                    <div class="col-md-4">
                                        <?php if (isset($group_links['sub_name']) && $group_links['sub_name']) { ?>
                                            <?php if (!empty($group_links['sub_name_tag'])) { ?>
                                                <?php echo ('<' . $group_links['sub_name_tag'] . ' class="h5">' . $group_links['sub_name'] . '</' . $group_links['sub_name_tag'] . '>'); ?>
                                            <?php } else { ?>
                                                <p class="h5"><?php echo $group_links['sub_name']; ?></p>
                                            <?php } ?>
                                        <?php } ?>
                                        <ul class="list-unstyled font-size-sm">
                                            <?php if (isset($group_links['items']) && count($group_links['items'])) { ?>
                                                <?php foreach ($group_links['items'] as $item_index => $item) { ?>
                                                    <li class="d-flex align-items-center justify-content-between">
                                                        <a href="<?php echo($link_download . '&setting=' . $table_index . '.' . $group_index . '.' . $item_index); ?>"
                                                           class="nav-link-style">
                                                            <i class="czi-download mr-2"></i>
                                                            <?php echo $item['name']; ?>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if ($group_links['name']) { ?>
                                                <li>...</li>
                                                <li>
                                                    <hr>
                                                </li>
                                                <li class="d-flex align-items-center justify-content-between">
                                                    <a href="<?php echo($link_download . '&setting=' . $table_index . '.' . $group_index); ?>"
                                                       class="nav-link-style">
                                                        <i class="czi-download mr-2"></i><?php echo $group_links['name']; ?>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        <?php if (!empty($text['description'])) {
            echo html_entity_decode($text['description'], ENT_QUOTES, 'UTF-8');
        } ?>
    </section>
<?php echo $footer; ?>
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-price-offer" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-price-offer" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="price_offer_status" id="input-status" class="form-control">
                <option value="1"<?php echo $status ? ' selected="selected"' : ''; ?>><?php echo $text_enabled; ?></option>
                <option value="0"<?php echo $status ? '' : ' selected="selected"'; ?>><?php echo $text_disabled; ?></option>
              </select>
            </div>
          </div>
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-sections" data-toggle="tab"><?php echo $tab_sections; ?></a></li>
            <li><a href="#tab-products" data-toggle="tab"><?php echo $tab_products; ?></a></li>
            <li><a href="#tab-page" data-toggle="tab"><?php echo $tab_page; ?></a></li>
            <li><a href="#tab-file" data-toggle="tab"><?php echo $tab_file; ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-sections">
              <div id="price-offer-sections" data-config="<?php echo htmlspecialchars($config, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row">
                  <div class="col-sm-4">
                    <div class="clearfix">
                      <strong class="pull-left"><?php echo $text_categories; ?></strong>
                      <span class="pull-right"><a href="#" @click.prevent="selectAll"><?php echo $text_select_all; ?></a> / <a href="#" @click.prevent="selected = []"><?php echo $text_select_none; ?></a></span>
                    </div>
                    <input type="text" v-model="filter" placeholder="<?php echo $text_filter; ?>" class="form-control input-sm price-offer-filter">
                    <div class="well well-sm price-offer-categories">
                      <div class="checkbox" v-for="category in visibleCategories" :key="category.id">
                        <label><input type="checkbox" v-model="selected" :value="category.id"> {{ category.name }} <span class="text-muted">({{ category.products }})</span></label>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-8">
                    <fieldset>
                      <legend><?php echo $text_add; ?></legend>
                      <div class="form-group">
                        <label class="col-sm-3 control-label" for="input-target"><?php echo $entry_target; ?></label>
                        <div class="col-sm-9">
                          <select id="input-target" v-model="target" class="form-control">
                            <option v-for="option in targets" :value="option.value">{{ option.text }}</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label" for="input-name"><?php echo $entry_name; ?></label>
                        <div class="col-sm-9">
                          <input type="text" id="input-name" v-model="name" @keydown.enter.prevent="add" class="form-control">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo $entry_discounts; ?></label>
                        <div class="col-sm-2">
                          <input type="text" v-model="discounts[0]" @keydown.enter.prevent="add" placeholder="<?php echo $entry_small; ?>" class="form-control">
                        </div>
                        <div class="col-sm-2">
                          <input type="text" v-model="discounts[1]" @keydown.enter.prevent="add" placeholder="<?php echo $entry_medium; ?>" class="form-control">
                        </div>
                        <div class="col-sm-2">
                          <input type="text" v-model="discounts[2]" @keydown.enter.prevent="add" placeholder="<?php echo $entry_large; ?>" class="form-control">
                        </div>
                        <div class="col-sm-3">
                          <button type="button" @click="add" class="btn btn-success btn-block"><i class="fa fa-plus"></i> <?php echo $button_add; ?></button>
                        </div>
                      </div>
                    </fieldset>
                    <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $text_help; ?></div>
                  </div>
                </div>
                <p v-if="!sections.length" class="text-center text-muted"><?php echo $text_no_sections; ?></p>
                <div v-for="(section, s) in sections" class="panel panel-default">
                  <div class="panel-heading clearfix">
                    <div class="row">
                      <div class="col-sm-2">
                        <select :name="inputName(s, 'tag')" v-model="section.tag" class="form-control input-sm" title="<?php echo $entry_tag; ?>">
                          <option v-for="tag in tags" :value="tag">{{ tag }}</option>
                        </select>
                      </div>
                      <div class="col-sm-7">
                        <input type="text" :name="inputName(s, 'title')" v-model="section.title" placeholder="<?php echo $entry_section_title; ?>" class="form-control input-sm">
                      </div>
                      <div class="col-sm-3 text-right text-nowrap">
                        <button type="button" @click="move(sections, s, -1)" :disabled="s == 0" title="<?php echo $button_up; ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-up"></i></button>
                        <button type="button" @click="move(sections, s, 1)" :disabled="s == sections.length - 1" title="<?php echo $button_down; ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-down"></i></button>
                        <span class="label label-default"><?php echo $text_section; ?> {{ s + 1 }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div v-for="(group, g) in section.groups" class="price-offer-group">
                      <div class="row">
                        <div class="col-sm-2">
                          <select :name="inputName(s, 'groups', g, 'tag')" v-model="group.tag" class="form-control input-sm" title="<?php echo $entry_tag; ?>">
                            <option v-for="tag in tags" :value="tag">{{ tag }}</option>
                          </select>
                        </div>
                        <div class="col-sm-7">
                          <input type="text" :name="inputName(s, 'groups', g, 'title')" v-model="group.title" placeholder="<?php echo $entry_group_title; ?>" class="form-control input-sm">
                        </div>
                        <div class="col-sm-3 text-right text-nowrap">
                          <button type="button" @click="move(section.groups, g, -1)" :disabled="g == 0" title="<?php echo $button_up; ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-up"></i></button>
                          <button type="button" @click="move(section.groups, g, 1)" :disabled="g == section.groups.length - 1" title="<?php echo $button_down; ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-down"></i></button>
                          <span class="label label-info"><?php echo $text_group; ?> {{ s + 1 }}.{{ g + 1 }}</span>
                        </div>
                      </div>
                      <table class="table table-condensed">
                        <thead>
                          <tr>
                            <td><?php echo $entry_name; ?></td>
                            <td style="width: 25%;"><?php echo $entry_categories; ?></td>
                            <td style="width: 9%;"><?php echo $entry_small; ?></td>
                            <td style="width: 9%;"><?php echo $entry_medium; ?></td>
                            <td style="width: 9%;"><?php echo $entry_large; ?></td>
                            <td style="width: 1px;"></td>
                          </tr>
                        </thead>
                        <tbody>
                          <tr class="active">
                            <td><strong><?php echo $text_default; ?></strong></td>
                            <td></td>
                            <td v-for="(value, i) in group.discounts"><input type="text" :name="inputName(s, 'groups', g, 'discounts', i)" v-model="group.discounts[i]" class="form-control input-sm"></td>
                            <td></td>
                          </tr>
                          <tr v-for="(offer, o) in group.offers" :key="offer.id">
                            <td>
                              <input type="hidden" :name="inputName(s, 'groups', g, 'offers', o, 'id')" :value="offer.id">
                              <input type="text" :name="inputName(s, 'groups', g, 'offers', o, 'name')" v-model="offer.name" class="form-control input-sm">
                            </td>
                            <td>
                              <input type="hidden" :name="inputName(s, 'groups', g, 'offers', o, 'categories')" :value="offer.categories.join(',')">
                              <small>{{ categoryNames(offer.categories) }}</small>
                            </td>
                            <td v-for="(value, i) in offer.discounts"><input type="text" :name="inputName(s, 'groups', g, 'offers', o, 'discounts', i)" v-model="offer.discounts[i]" :placeholder="group.discounts[i]" class="form-control input-sm"></td>
                            <td class="text-nowrap">
                              <button type="button" @click="move(group.offers, o, -1)" :disabled="o == 0" title="<?php echo $button_up; ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-up"></i></button>
                              <button type="button" @click="move(group.offers, o, 1)" :disabled="o == group.offers.length - 1" title="<?php echo $button_down; ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-down"></i></button>
                              <button type="button" @click="showProducts(offer)" class="btn btn-primary btn-sm"><?php echo $button_products; ?></button>
                              <button type="button" @click="removeOffer(s, g, o)" title="<?php echo $button_remove; ?>" class="btn btn-danger btn-sm"><i class="fa fa-minus-circle"></i></button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-products">
              <div id="price-offer-products">
                <p v-if="!offer" class="text-center text-muted"><?php echo $text_no_offer; ?></p>
                <template v-else>
                  <h4>{{ offer.name }} <small>{{ totalText }}</small></h4>
                  <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $help_product_discounts; ?></div>
                  <table class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <td style="width: 1px;"></td>
                        <td><?php echo $column_product; ?></td>
                        <td class="text-right"><?php echo $column_price; ?></td>
                        <td style="width: 30%;"><?php echo $entry_product_discounts; ?></td>
                        <td class="text-center"><?php echo $text_in_offer; ?></td>
                        <td style="width: 1px;"><?php echo $column_action; ?></td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="product in products" :key="product.product_id" :class="{ 'text-muted': product.excluded }">
                        <td><img :src="product.image" alt="" class="img-thumbnail"></td>
                        <td>{{ product.name }}<br><small class="text-muted">{{ product.model }}</small></td>
                        <td class="text-right">{{ product.price.toFixed(2) }}</td>
                        <td :class="{ 'has-success': product.state == 'saved', 'has-error': product.state == 'error' }">
                          <input type="text" v-model="product.discounts" @input="product.state = ''" @keydown.enter.prevent="saveDiscounts(product)" placeholder="10>5|50>10" class="form-control input-sm">
                        </td>
                        <td class="text-center"><input type="checkbox" :checked="!product.excluded" @change="saveExcluded(product, !$event.target.checked)"></td>
                        <td class="text-nowrap">
                          <button type="button" @click="saveDiscounts(product)" title="<?php echo $button_save; ?>" class="btn btn-success btn-sm"><i class="fa fa-save"></i></button>
                          <a :href="editUrl(product)" target="_blank" title="<?php echo $button_edit; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                        </td>
                      </tr>
                      <tr v-if="!products.length && !loading">
                        <td colspan="6" class="text-center"><?php echo $text_no_products; ?></td>
                      </tr>
                    </tbody>
                  </table>
                  <price-offer-pager :page="page" :pages="pages" @change="load"></price-offer-pager>
                </template>
              </div>
            </div>
            <div class="tab-pane" id="tab-page">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-h1"><?php echo $entry_h1; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="price_offer_page[h1]" value="<?php echo $page['h1']; ?>" id="input-h1" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-sub-title"><?php echo $entry_sub_title; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="price_offer_page[sub_title]" value="<?php echo $page['sub_title']; ?>" id="input-sub-title" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-meta-title"><?php echo $entry_meta_title; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="price_offer_page[meta_title]" value="<?php echo $page['meta_title']; ?>" id="input-meta-title" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-meta-description"><?php echo $entry_meta_description; ?></label>
                <div class="col-sm-10">
                  <textarea name="price_offer_page[meta_description]" rows="3" id="input-meta-description" class="form-control"><?php echo $page['meta_description']; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-top"><span data-toggle="tooltip" title="<?php echo $help_html; ?>"><?php echo $entry_top; ?></span></label>
                <div class="col-sm-10">
                  <textarea name="price_offer_page[top]" rows="5" id="input-top" class="form-control"><?php echo $page['top']; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-bottom"><span data-toggle="tooltip" title="<?php echo $help_html; ?>"><?php echo $entry_bottom; ?></span></label>
                <div class="col-sm-10">
                  <textarea name="price_offer_page[bottom]" rows="10" id="input-bottom" class="form-control"><?php echo $page['bottom']; ?></textarea>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-file">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-company"><span data-toggle="tooltip" title="<?php echo $help_company; ?>"><?php echo $entry_company; ?></span></label>
                <div class="col-sm-10">
                  <textarea name="price_offer_company" rows="4" id="input-company" class="form-control"><?php echo $company; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-filename"><span data-toggle="tooltip" title="<?php echo $help_filename; ?>"><?php echo $entry_filename; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="price_offer_filename" value="<?php echo $filename; ?>" placeholder="price-offer" id="input-filename" class="form-control">
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<style>
.price-offer-filter {
  margin: 8px 0;
}
.price-offer-categories {
  height: 320px;
  overflow-y: auto;
}
.price-offer-categories .checkbox {
  margin: 0 0 4px;
}
.price-offer-group + .price-offer-group {
  border-top: 1px solid #ddd;
  padding-top: 15px;
}
.price-offer-group .table {
  margin: 10px 0 15px;
}
</style>
<?php echo $footer; ?>

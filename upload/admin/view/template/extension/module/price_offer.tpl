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
          <div id="price-offer-sections" data-config="<?php echo htmlspecialchars($config, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row">
              <div class="col-sm-4">
                <strong><?php echo $text_categories; ?></strong>
                <div class="well well-sm price-offer-categories">
                  <div class="checkbox" v-for="category in categories" :key="category.id">
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
                      <div class="input-group">
                        <input type="text" id="input-name" v-model="name" @keydown.enter.prevent="add" class="form-control">
                        <span class="input-group-btn"><button type="button" @click="add" class="btn btn-success"><i class="fa fa-plus"></i> <?php echo $button_add; ?></button></span>
                      </div>
                    </div>
                  </div>
                </fieldset>
              </div>
            </div>
            <p v-if="!sections.length" class="text-center text-muted"><?php echo $text_no_sections; ?></p>
            <div v-for="(section, s) in sections" class="panel panel-default">
              <div class="panel-heading clearfix">
                <div class="row">
                  <div class="col-sm-9">
                    <input type="text" :name="inputName(s, 'title')" v-model="section.title" placeholder="<?php echo $entry_section_title; ?>" class="form-control input-sm">
                  </div>
                  <div class="col-sm-3 text-right">
                    <span class="label label-default"><?php echo $text_section; ?> {{ s + 1 }}</span>
                  </div>
                </div>
              </div>
              <div class="panel-body">
                <div v-for="(group, g) in section.groups" class="price-offer-group">
                  <div class="row">
                    <div class="col-sm-9">
                      <input type="text" :name="inputName(s, 'groups', g, 'title')" v-model="group.title" placeholder="<?php echo $entry_group_title; ?>" class="form-control input-sm">
                    </div>
                    <div class="col-sm-3 text-right">
                      <span class="label label-info"><?php echo $text_group; ?> {{ s + 1 }}.{{ g + 1 }}</span>
                    </div>
                  </div>
                  <table class="table table-condensed">
                    <thead>
                      <tr>
                        <td><?php echo $entry_name; ?></td>
                        <td style="width: 25%;"><?php echo $entry_categories; ?></td>
                        <td style="width: 1px;"></td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(offer, o) in group.offers">
                        <td>
                          <input type="text" :name="inputName(s, 'groups', g, 'offers', o, 'name')" v-model="offer.name" class="form-control input-sm">
                        </td>
                        <td>
                          <input type="hidden" :name="inputName(s, 'groups', g, 'offers', o, 'categories')" :value="offer.categories.join(',')">
                          <small>{{ categoryNames(offer.categories) }}</small>
                        </td>
                        <td class="text-nowrap">
                          <button type="button" @click="removeOffer(s, g, o)" title="<?php echo $button_remove; ?>" class="btn btn-danger btn-sm"><i class="fa fa-minus-circle"></i></button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
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
<script type="text/javascript"><!--
$(function () {
  var root = document.getElementById('price-offer-sections');

  if (!root) {
    return;
  }

  var config = JSON.parse(root.getAttribute('data-config'));

  new Vue({
    el: root,
    data: {
      sections: config.sections,
      categories: config.categories,
      selected: [],
      target: 'section',
      name: ''
    },
    computed: {
      targets: function () {
        var targets = [{ value: 'section', text: config.text.new_section }];

        this.sections.forEach(function (section, s) {
          targets.push({ value: 'group.' + s, text: config.text.new_group.replace('%s', s + 1) });

          section.groups.forEach(function (group, g) {
            targets.push({ value: 'offer.' + s + '.' + g, text: config.text.new_offer.replace('%s', (s + 1) + '.' + (g + 1)) });
          });
        });

        return targets;
      }
    },
    methods: {
      add: function () {
        if (!this.selected.length) {
          alert(config.text.categories);
          return;
        }

        if (!this.name.trim()) {
          alert(config.text.name);
          return;
        }

        var offer = { name: this.name.trim(), categories: this.selected.slice() };
        var group = { title: '', offers: [offer] };
        var path = this.target.split('.');

        if (path[0] === 'offer') {
          this.sections[path[1]].groups[path[2]].offers.push(offer);
        } else if (path[0] === 'group') {
          this.sections[path[1]].groups.push(group);
        } else {
          this.sections.push({ title: '', groups: [group] });
        }

        this.selected = [];
        this.name = '';
      },
      // Пустые группа и раздел не сохраняются, поэтому удаляются вместе с последним предложением.
      removeOffer: function (s, g, o) {
        var groups = this.sections[s].groups;

        groups[g].offers.splice(o, 1);

        if (!groups[g].offers.length) {
          groups.splice(g, 1);
        }

        if (!groups.length) {
          this.sections.splice(s, 1);
        }

        this.target = 'section';
      },
      categoryNames: function (ids) {
        var names = {};

        this.categories.forEach(function (category) {
          names[category.id] = category.name;
        });

        return ids.map(function (id) {
          return names[id] || id;
        }).join(', ');
      },
      inputName: function () {
        return 'price_offer_sections[' + Array.prototype.join.call(arguments, '][') + ']';
      }
    }
  });
});
//--></script>
<?php echo $footer; ?>

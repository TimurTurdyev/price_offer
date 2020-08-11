<?php echo $header; ?>
<?php echo $column_left; ?>

    <div id="content">
        <style>
            label.disabled {
                opacity: .4;
            }

            input.success {
                border-color: #8EBB6C;
                outline: 0;
                -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgb(142 187 108);
                box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgb(142 187 108);
            }

            input.not_export {
                opacity: .4;
                background: #f56b6b;
            }

            .tableFixHead {
                overflow-y: auto;
                height: 50rem;
            }

            .tableFixHead:not(.not_sticky) thead th {
                position: sticky;
                top: 0;
                background: #eee;
            }

            .tableFixHead tfoot td {
                position: sticky;
                bottom: 0;
                background: #eee;
            }

            .block_pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
        </style>
        <div class="page-header">
            <div class="container-fluid">
                <div class="pull-right">
                    <button type="button" onclick="$('#form-account').submit();" data-toggle="tooltip"
                            title="<?php echo $button_save; ?>"
                            class="btn btn-primary"><i class="fa fa-save"></i></button>
                    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                       class="btn btn-default"><i class="fa fa-reply"></i></a></div>
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

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
                </div>

                <div id="app" class="panel-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab-table" data-toggle="tab">Таблица</a></li>
                        <li><a href="#tab-meta" data-toggle="tab">Мета и описание</a></li>
                    </ul>
                    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-account"
                          class="form-horizontal">
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-table">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h3 class="pull-left">Категории</h3>
                                        <div class="pull-right">
                                            <a v-on:click.stop.prevent="categoryCheck">Выделить все</a>
                                            /
                                            <a v-on:click.stop.prevent="categoryUnCheck">Снять выделение</a>
                                        </div>

                                        <div class="well well-sm"
                                             style="min-height: 150px;max-height: 279px;overflow-y: auto; overflow-x: hidden; width: 100%;">
                                            <table class="table table-striped">
                                                <tr v-for="category in categories">
                                                    <td class="checkbox">
                                                        <label>
                                                            <input type="checkbox" v-model="categorySelected"
                                                                   :value="category.category_id"/>
                                                            <span v-html="'[' + category.category_id + '] ' + category.name + ' (' + category.product_count + ')'"></span>
                                                        </label>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h3>Описание модуля</h3>
                                        <?php if (isset($error_warning) && $error_warning) { ?>
                                            <div class="alert alert-danger"><i
                                                        class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
                                                <button type="button" class="close" data-dismiss="alert">&times;
                                                </button>
                                            </div>
                                        <?php } ?>
                                        <?php if (isset($success) && $success) { ?>
                                            <div class="alert alert-success"><i
                                                        class="fa fa-exclamation-circle"></i> <?php echo $success; ?>
                                                <button type="button" class="close" data-dismiss="alert">&times;
                                                </button>
                                            </div>
                                        <?php } ?>
                                        <div class="well">
                                            <p><b>Общий заголовок</b> - определяет название перед группой блоков с
                                                сылками.</p>
                                            <p><b>Заголовок группы</b> - определяет название перед группой блока с
                                                сылками, если пусто не показывается.</p>
                                            <p><b>Тег</b> - непосредственно тег в который будет вставлен общий заголовок или заголовок группы.</p>
                                            <p>Далее в таблице есть тип. Тип определяет сущность в группе настроек и
                                                напрямую
                                                влияет на отображение.
                                                <br>
                                                <b>Parent</b> - Родительский элемент. Если название пусто, то не
                                                учавствует
                                                в отображении.
                                                <br>
                                                <b>Child</b> - Вложенный элемент. Учавствует в структуре формирования
                                                блока.
                                            </p>
                                            <p><b>Coefficient</b> - Указанный в таблице, будет присвоен в товары
                                                учавствующие в выборке, при условии если <u>coefficient</u> не прописан
                                                в самом товаре. Имеет более низкий приоретет.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Название</label>
                                    <div class="col-md-2">
                                        <select class="form-control input-lg" v-model="changeSelectedToTable">
                                            <option disabled value="">Выберите один из вариантов</option>
                                            <option value="new">Добавить таблицу</option>
                                            <template v-for="(table, index) in existingSettings">
                                                <template v-for="group in table">
                                                    <option :value="'child.' + index + '.' + group">[{{index + '.' +
                                                        group}}]
                                                        Добавить в группу
                                                    </option>
                                                    <option :value="'group.' + index + '.' + group">[{{index + '.' +
                                                        group}}]
                                                        Создать группу после
                                                    </option>
                                                </template>
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control input-lg" placeholder="Название"
                                               v-model="newName">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control input-lg" placeholder="Coefficient"
                                               v-model="newCoefficient">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-success btn-block btn-lg"
                                                @click.stop.prevent="copyTable">
                                            Скопировать в таблицу
                                        </button>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <div class="col-md-8 tableFixHead not_sticky">
                                        <div v-for="(group, groupIndex) in settings">
                                            <hr v-if="groupIndex > 0">
                                            <div class="form-group">
                                                <label :for="'group_name_tag' + groupIndex"
                                                       class="control-label col-md-2">
                                                    Тег
                                                </label>
                                                <div class="col-md-2">
                                                    <select :name="'price_offer_setting[' + groupIndex + '][group_tag]'" :id="'group_name_tag' + groupIndex" class="form-control">
                                                        <template v-for="tag in tags">
                                                            <option :value="tag" v-if="group.group_tag === tag" selected>{{tag}}</option>
                                                            <option :value="tag" v-else>{{tag}}</option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <label :for="'group_name' + groupIndex" class="control-label col-md-2">
                                                    Общий заголовок
                                                </label>
                                                <div class="col-md-6">
                                                    <input type="text"
                                                           :id="'group_name' + groupIndex"
                                                           :value="group.group_name"
                                                           :name="'price_offer_setting[' + groupIndex + '][group_name]'"
                                                           class="form-control"
                                                    >
                                                </div>
                                            </div>
                                            <hr>
                                            <div v-for="(parent, parentIndex) in group.groups">
                                                <div class="form-group">
                                                    <label :for="'group_name_tag' + groupIndex + parentIndex"
                                                           class="control-label col-md-2">
                                                        Тег
                                                    </label>
                                                    <div class="col-md-2">
                                                        <select :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][sub_name_tag]'" :id="'group_name_tag' + groupIndex + parentIndex" class="form-control">
                                                            <template v-for="tag in tags">
                                                                <option :value="tag" v-if="parent.sub_name_tag === tag" selected>{{tag}}</option>
                                                                <option :value="tag" v-else>{{tag}}</option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                    <label :for="'group_name' + groupIndex + parentIndex"
                                                           class="control-label col-md-2">
                                                        Заголовок группы
                                                    </label>
                                                    <div class="col-md-6">
                                                        <input type="text"
                                                               :id="'parent_sub_name' + groupIndex + parentIndex"
                                                               v-model="parent.sub_name"
                                                               :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][sub_name]'"
                                                               class="form-control"
                                                        >
                                                    </div>
                                                </div>
                                                <br>
                                                <table class="table table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>({{groupIndex}})</th>
                                                        <th>Тип</th>
                                                        <th>Название</th>
                                                        <th>Параметры поиска товара</th>
                                                        <th>Коэффициент</th>
                                                        <th>Действие</th>
                                                    </tr>
                                                    </thead>
                                                    <tfoot>
                                                    <tr v-for="(children, index) in parent.items"
                                                        :id="'children' + index">
                                                        <td scope="row">{{parentIndex}}.{{index}}</td>
                                                        <td>Child</td>
                                                        <td>
                                                            <input type="text"
                                                                   :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][items][' + index + '][name]'"
                                                                   v-model="children.name"
                                                                   class="form-control input-sm">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                   :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][items][' + index + '][categories]'"
                                                                   v-model="children.categories" readonly
                                                                   class="form-control input-sm">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                   :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][items][' + index + '][coefficient]'"
                                                                   v-model="children.coefficient"
                                                                   placeholder="Coefficient"
                                                                   class="form-control input-sm">
                                                        </td>
                                                        <td>
                                                            <button
                                                                    v-on:click.stop.prevent="loadProducts(children.categories)"
                                                                    type="button"
                                                                    class="btn btn-primary btn-sm"
                                                            >Загрузить товары
                                                            </button>
                                                            <button
                                                                    v-on:click.stop.prevent="removeRow(parent.items, index)"
                                                                    type="button"
                                                                    class="btn btn-danger btn-sm"
                                                            >Удалить
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    </tfoot>
                                                    <tbody>
                                                    <tr>
                                                        <td scope="row">{{parentIndex}}</td>
                                                        <td>Parent</td>
                                                        <td>
                                                            <input type="text"
                                                                   :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][name]'"
                                                                   v-model="parent.name"
                                                                   class="form-control input-sm">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                   :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][categories]'"
                                                                   v-model="parent.categories" readonly
                                                                   class="form-control input-sm">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                   :name="'price_offer_setting[' + groupIndex + '][groups][' + parentIndex + '][coefficient]'"
                                                                   v-model="parent.coefficient"
                                                                   placeholder="Coefficient"
                                                                   class="form-control input-sm">
                                                        </td>
                                                        <td>
                                                            <button
                                                                    v-on:click.stop.prevent="loadProducts(parent.categories)"
                                                                    type="button"
                                                                    class="btn btn-primary btn-sm"
                                                            >Загрузить товары
                                                            </button>
                                                            <button
                                                                    v-on:click.stop.prevent="removeRow(group.groups, parentIndex, groupIndex)"
                                                                    type="button"
                                                                    class="btn btn-danger btn-sm"
                                                            >Удалить
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 tableFixHead">
                                        <table class="table table-hover" id="js_table_products">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Товар</th>
                                                <th>Наименование</th>
                                                <th width="140">Коэффициент</th>
                                                <th width="92">Действие</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(product, index) in products">
                                                <td>{{product.product_id}}</td>
                                                <td>
                                                    <img :src="image_catalog + (product.image ? product.image : 'no_image.png')"
                                                         width="60"></td>
                                                <td>{{product.name}}</td>
                                                <td><input type="text" v-model="product.coefficient"
                                                           class="form-control input-sm"
                                                           placeholder="Coefficient"
                                                           v-bind:class="[product.class === 'success' ? 'success' : product.jan === 'not_export' ? 'not_export' : '']">
                                                </td>
                                                <td>
                                                    <button v-on:click.stop.prevent="saveCoefficient(index)"
                                                            type="button"
                                                            class="btn btn-success btn-xs"><i
                                                                class="fa fa-edit"></i></button>
                                                    <button v-on:click.stop.prevent="notExport(index)"
                                                            type="button" class="btn btn-danger btn-xs"><i
                                                                class="fa fa-remove"></i></button>
                                                    <a :href="'index.php?route=catalog/product/edit&product_id=' + product.product_id + '&token=' + moduleToken"
                                                       target="_blank" class="btn btn-info btn-xs"><i
                                                                class="fa fa-external-link"></i></a>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <tfoot>
                                            <tr v-if="productTotal > 0">
                                                <td colspan="5">
                                                    <div class="block_pagination">
                                                        <template>
                                                            <paginate
                                                                    :page-count="paginationPages"
                                                                    :page-range="3"
                                                                    :margin-pages="2"
                                                                    :click-handler="paginationClick"
                                                                    :prev-text="'Prev'"
                                                                    :next-text="'Next'"
                                                                    :container-class="'pagination'"
                                                                    :page-class="'page-item'">
                                                            </paginate>
                                                        </template>
                                                        <div>
                                                            <b>Всего: ({{productTotal}})</b>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-else>
                                                <td colspan="5"><b>Пусто...</b></td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab-meta">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"
                                           for="input-meta-h1">Meta h1</label>
                                    <div class="col-sm-10">
                                        <input name="price_offer_text[h1]" value="<?php echo $price_offer_text['h1']; ?>" id="input-meta-h1" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"
                                           for="input-sub-title">Sub title</label>
                                    <div class="col-sm-10">
                                        <input name="price_offer_text[sub_title]" value="<?php echo $price_offer_text['sub_title']; ?>" id="input-sub-title" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"
                                           for="input-title">Meta title</label>
                                    <div class="col-sm-10">
                                        <textarea name="price_offer_text[meta_title]" id="input-meta-title" cols="30" rows="10" class="form-control"><?php echo $price_offer_text['meta_title']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"
                                           for="input-meta-description">Meta description</label>
                                    <div class="col-sm-10">
                                        <textarea name="price_offer_text[meta_description]" id="input-meta-description" cols="30" rows="10" class="form-control"><?php echo $price_offer_text['meta_description']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"
                                           for="input-mini-description">Мини описание</label>
                                    <div class="col-sm-10">
                                        <textarea name="price_offer_text[mini_description]" id="input-mini-description" cols="30" rows="10" class="form-control"><?php echo $price_offer_text['mini_description']; ?></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"
                                           for="input-description">Основное описание</label>
                                    <div class="col-sm-10">
                                        <textarea name="price_offer_text[description]" id="input-description" cols="30" rows="10" class="form-control"><?php echo $price_offer_text['description']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-status"><?php echo $entry_status; ?></label>
                            <div class="col-sm-10">
                                <select name="price_offer_status" id="input-status" class="form-control">
                                    <?php if ($price_offer_status) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        Vue.component('paginate', VuejsPaginate);
        var app = new Vue({
            el: '#app',
            data: {
                moduleToken: '<?php echo $token; ?>',
                image_catalog: '<?php echo $image_catalog; ?>',
                categorySelected: [],
                categories: JSON.parse('<?php echo json_encode($categories, 1); ?>'),
                settings: JSON.parse('<?php echo json_encode($price_offer_setting, 1); ?>'),
                changeSelectedToTable: '',
                settingsNotEmpty: false,
                existingSettings: [],
                newName: '',
                newCoefficient: '',
                products: [],
                productTotal: 0,
                paginationPages: 0,
                productCategories: '',
                tags: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span']
            },
            methods: {
                categoryCheck() {
                    this.categorySelected = this.categories.map(item => item.category_id);
                },
                categoryUnCheck() {
                    this.categorySelected = [];
                },
                changeTable() {
                    console.log(event)
                },
                copyTable() {
                    if (this.categorySelected.length === 0) {
                        alert('Выберите категорю(и)');
                        return;
                    }
                    if (this.changeSelectedToTable === 'new') {
                        this.settings.push({
                            group_name: '',
                            groups: [{
                                name: this.newName,
                                categories: this.categorySelected.join(','),
                                coefficient: this.newCoefficient,
                                items: []
                            }],
                        });
                    }
                    var props = this.changeSelectedToTable.split('.');

                    if (props.length > 2 && this.settings[props[1]]) {
                        if (props[0] === 'child') {
                            this.settings[props[1]].groups[props[2]].items.push({
                                name: this.newName,
                                categories: this.categorySelected.join(','),
                                coefficient: this.newCoefficient,
                            });
                        } else if (props[0] === 'group') {
                            this.settings[props[1]].groups.push({
                                name: this.newName,
                                categories: this.categorySelected.join(','),
                                coefficient: this.newCoefficient,
                                items: []
                            });
                        }
                        //this.settings[this.changeSelectedToTable].groups
                    }
                    this.categorySelected = [];
                    this.existingSettings = this.settings.map((item) => item.groups.map((group, i) => i));
                    this.clearData();
                },
                loadProducts(categories) {
                    var url = 'index.php?route=extension/module/price_offer/products&token=' + this.moduleToken + '&categories=' + categories;
                    this.getProducts(url, categories);
                },
                removeRow(parent, index, group) {
                    if (group !== undefined && parent.length === 1) {
                        this.$delete(this.settings, group);
                    } else {
                        this.$delete(parent, index);
                    }
                    this.clearData();
                },
                paginationClick(pageNum) {
                    var url = 'index.php?route=extension/module/price_offer/products&token=' + this.moduleToken;
                    url += '&categories=' + this.productCategories;
                    url += '&offset=' + ((pageNum - 1) * 100);
                    this.getProducts(url, this.productCategories);
                },
                clearData() {
                    this.products = [];
                    this.productTotal = 0;
                    this.paginationPages = 0;
                    this.productCategories = '';
                    this.existingSettings = this.settings.map((item) => item.groups.map((group, i) => i));
                },
                getProducts(url, categories) {
                    $.get(url, (response) => {
                        if (response.products) {
                            this.products = response.products;
                            this.productTotal = response.total;
                            this.paginationPages = Math.ceil(response.total / 100);
                            this.productCategories = categories;
                        } else {
                            this.clearData();
                        }
                    })
                },
                saveCoefficient(productIndex) {
                    var url = 'index.php?route=extension/module/price_offer/change&token=' + this.moduleToken;
                    url += '&product_id=' + this.products[productIndex].product_id;
                    url += '&coefficient=' + this.products[productIndex].coefficient;
                    url += '&export=';
                    $.get(url, (response) => {
                        if (response['update']) {
                            this.products[productIndex].class = 'success';
                            this.products[productIndex].jan = '';
                        } else {
                            var message = [];
                            for (var key in response) {
                                message.push(key + ': ' + response[key]);
                            }
                            if (message.length) {
                                alert(message.join('\n'))
                            }
                        }
                    });
                },
                notExport(productIndex) {
                    var url = 'index.php?route=extension/module/price_offer/change&token=' + this.moduleToken;
                    url += '&product_id=' + this.products[productIndex].product_id;
                    url += '&coefficient=' + this.products[productIndex].coefficient;
                    url += '&export=not_export';
                    $.get(url, (response) => {
                        if (response['update']) {
                            this.$delete(this.products[productIndex], 'class');
                            this.products[productIndex].jan = 'not_export';
                        } else {
                            var message = [];
                            for (var key in response) {
                                message.push(key + ': ' + response[key]);
                            }
                            if (message.length) {
                                alert(message.join('\n'))
                            }
                        }
                    });
                },
            },
            created: function () {
                this.settings.forEach((groups) => {
                    groups.groups.forEach((group) => {
                        if (!group.items) group.items = [];
                    });
                })
                this.clearData();
            }
        });
    </script>
<?php echo $footer; ?>
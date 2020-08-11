$(function () {
  var root = document.getElementById('price-offer-sections');

  if (!root) {
    return;
  }

  var config = JSON.parse(root.getAttribute('data-config'));

  // Общие данные двух вкладок: разделы и товары выбранного предложения.
  var state = {
    sections: config.sections,
    categories: config.categories,
    tags: config.tags,
    filter: '',
    selected: [],
    target: 'section',
    name: '',
    discounts: ['', '', ''],
    offer: null,
    products: [],
    total: 0,
    page: 1,
    loading: false
  };

  Vue.component('price-offer-pager', {
    props: ['page', 'pages'],
    computed: {
      numbers: function () {
        var numbers = [];

        for (var n = Math.max(1, this.page - 2); n <= Math.min(this.pages, this.page + 2); n++) {
          numbers.push(n);
        }

        return numbers;
      }
    },
    methods: {
      go: function (page) {
        if (page >= 1 && page <= this.pages && page !== this.page) {
          this.$emit('change', page);
        }
      }
    },
    template: '<ul v-if="pages > 1" class="pagination">' +
      '<li :class="{ disabled: page == 1 }"><a href="#" @click.prevent="go(page - 1)">&laquo;</a></li>' +
      '<li v-if="numbers[0] > 1"><a href="#" @click.prevent="go(1)">1</a></li>' +
      '<li v-if="numbers[0] > 2" class="disabled"><span>...</span></li>' +
      '<li v-for="n in numbers" :class="{ active: n == page }"><a href="#" @click.prevent="go(n)">{{ n }}</a></li>' +
      '<li v-if="numbers[numbers.length - 1] < pages - 1" class="disabled"><span>...</span></li>' +
      '<li v-if="numbers[numbers.length - 1] < pages"><a href="#" @click.prevent="go(pages)">{{ pages }}</a></li>' +
      '<li :class="{ disabled: page == pages }"><a href="#" @click.prevent="go(page + 1)">&raquo;</a></li>' +
      '</ul>'
  });

  var products = new Vue({
    el: '#price-offer-products',
    data: state,
    computed: {
      pages: function () {
        return Math.ceil(this.total / config.limit);
      },
      totalText: function () {
        return config.text.total.replace('%s', this.total);
      }
    },
    methods: {
      open: function (offer) {
        this.offer = { name: offer.name, categories: offer.categories.join(',') };
        this.products = [];
        this.total = 0;
        this.load(1);
      },
      load: function (page) {
        var self = this;

        this.loading = true;

        $.getJSON(config.products, { categories: this.offer.categories, page: page }).done(function (json) {
          self.page = page;
          self.total = json.total;
          self.products = json.products.map(function (product) {
            product.state = '';
            return product;
          });
        }).fail(function () {
          alert(config.text.load);
        }).always(function () {
          self.loading = false;
        });
      },
      save: function (product, fields) {
        fields.product_id = product.product_id;

        $.post(config.save, fields, null, 'json').done(function (json) {
          if (json.error) {
            product.state = 'error';
            alert(json.error);
            return;
          }

          if (json.discounts !== undefined) {
            product.discounts = json.discounts;
          }

          if (fields.excluded !== undefined) {
            product.excluded = !!fields.excluded;
          }

          product.state = 'saved';
        }).fail(function () {
          product.state = 'error';
          alert(config.text.load);
        });
      },
      saveDiscounts: function (product) {
        this.save(product, { discounts: product.discounts });
      },
      saveExcluded: function (product, excluded) {
        this.save(product, { excluded: excluded ? 1 : 0 });
      },
      editUrl: function (product) {
        return config.edit + '&product_id=' + product.product_id;
      }
    }
  });

  new Vue({
    el: root,
    data: state,
    computed: {
      visibleCategories: function () {
        var filter = this.filter.toLowerCase();

        return this.categories.filter(function (category) {
          return category.name.toLowerCase().indexOf(filter) !== -1;
        });
      },
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
      selectAll: function () {
        this.selected = this.visibleCategories.map(function (category) {
          return category.id;
        });
      },
      add: function () {
        if (!this.selected.length) {
          alert(config.text.categories);
          return;
        }

        if (!this.name.trim()) {
          alert(config.text.name);
          return;
        }

        var offer = { id: this.nextId(), name: this.name.trim(), categories: this.selected.slice(), discounts: this.discounts.slice() };
        var group = { title: '', tag: 'h4', discounts: ['', '', ''], offers: [offer] };
        var path = this.target.split('.');

        if (path[0] === 'offer') {
          this.sections[path[1]].groups[path[2]].offers.push(offer);
        } else if (path[0] === 'group') {
          this.sections[path[1]].groups.push(group);
        } else {
          this.sections.push({ title: '', tag: 'h2', groups: [group] });
        }

        this.selected = [];
        this.name = '';
        this.discounts = ['', '', ''];
      },
      nextId: function () {
        var max = 0;

        this.sections.forEach(function (section) {
          section.groups.forEach(function (group) {
            group.offers.forEach(function (offer) {
              max = Math.max(max, offer.id);
            });
          });
        });

        return max + 1;
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
      move: function (list, index, step) {
        list.splice(index + step, 0, list.splice(index, 1)[0]);
      },
      showProducts: function (offer) {
        products.open(offer);
        $('a[href="#tab-products"]').tab('show');
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

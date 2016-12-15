jQuery(document).ready(function() {
  if (typeof jQuery().footable == "function") {
    initFiltering();
  }
});

function initFiltering() {
  FooTable.MyFiltering = FooTable.Filtering.extend({
    construct: function (instance) {
      this._super(instance);
      this.states = ['NY', 'ME', 'VT'];
      this.def = 'View All States';
      this.$state = null;
    },
    $create: function () {
      this._super();
      var self = this,
        $form_grp = jQuery('<div/>', {'class': 'form-group'})
          .append(jQuery('<label/>', {'class': 'sr-only', text: 'States'}))
          .prependTo(self.$form);

      self.$state = jQuery('<select/>', {'class': 'form-control stateInput'})
        .on('change', function () {
          self.filter();
        })
        .append(jQuery('<option/>', {text: self.def}))
        .appendTo($form_grp);

      jQuery.each(self.states, function (i, state) {
        self.$state.append(jQuery('<option/>').text(state));
      });
    },
    filter: function (query, columns) {
      var val = this.$state.val();
      if (val != this.def) this.addFilter('state', val, ['state']);
      else this.removeFilter('state');
      return this._super(query, columns);
    },
    clear: function () {
      this.$state.val(this.def);
      this.removeFilter('state');
      return this._super();
    }
  });

  FooTable.components.core.register('filtering', FooTable.MyFiltering);

  jQuery('#jobListings').footable();
}
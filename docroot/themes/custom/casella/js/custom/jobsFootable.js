jQuery(document).ready(function() {
  if (typeof jQuery().footable == "function") {
    initFiltering();
  }
});

function initFiltering() {
  FooTable.MyFiltering = FooTable.Filtering.extend({
    construct: function (instance) {
      var states = [], currentState;
      jQuery('.views-field-field-state').each(function (index, element) {
        currentState = jQuery(element).html().trim().replace(/(<!--(?:(?!-->).)*-->)/ig, '');
        if (states.indexOf(currentState) === -1) {
          states.push(currentState)
        }
      });

      states.sort();

      this._super(instance);
      this.states = states;
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
          updateURL();
        })
        .append(jQuery('<option/>', {text: self.def}))
        .appendTo($form_grp);

      jQuery.each(self.states, function (i, state) {
        self.$state.append(jQuery('<option/>').text(state));
      });

      // Check if URL variables are present.
      var argValues = getArgValues(location.search), changed = false;
      if (argValues.state != '') {
        jQuery('select.stateInput').val(argValues.state);
        changed = true;
      }
      if (argValues.keyword != '') {
        jQuery('.footable-filtering input').val(argValues.keyword);
        changed = true;
      }
      if (changed) {
        self.filter();
      }

      jQuery('.footable-filtering input').on('keyup', updateURL);
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

function getArgValues(args) {
  var tempArgs = args.replace(/^\?/, '').split('&'), retVal = {'state' : '', 'keyword' : ''}, i, tempArg;

  for (i = 0; i < tempArgs.length; i++) {
    tempArg = tempArgs[i].split('=');
    if (tempArg.length == 2) {
      retVal[tempArg[0]] = tempArg[1];
    }
  }

  return retVal;
}

function updateURL(newArgs) {
  if ("function" != typeof window.history.replaceState) {
    return;
  }

  var parsedArgs = [],
    stateVal = jQuery('select.stateInput').val(),
    keywordVal = jQuery('.footable-filtering input').val();

  if (stateVal != 'View All States') {
    parsedArgs.push('state=' + stateVal);
  }
  if (keywordVal != '') {
    parsedArgs.push('keyword=' + keywordVal);
  }

  window.history.replaceState('', '', location.origin + location.pathname + '?' + parsedArgs.join('&'));
}

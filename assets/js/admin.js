"use strict";!function(e){e(document).ready(function(){e("#radio-show-meta").addClass("hidden"),e("#gscr-live-radio-show").addClass("hidden"),e("#radio-show-meta").length>0&&(e('#taxonomy-tribe_events_cat input[type="checkbox"]').each(function(o,i){"Radio Show"==e(i).closest(".selectit").text().trim()&&e(i).prop("checked")&&(e("#radio-show-meta").removeClass("hidden"),e('#radio-show-meta input[name="_rbm_radio_show_live"]').is(":checked")&&e("#gscr-live-radio-show").removeClass("hidden"))}),e('#taxonomy-tribe_events_cat input[type="checkbox"]').on("change",function(o){"Radio Show"==e(this).closest(".selectit").text().trim()&&(e(this).prop("checked")?e("#radio-show-meta").removeClass("hidden"):(e("#radio-show-meta").addClass("hidden"),e("#gscr-live-radio-show").addClass("hidden")))}),e('#radio-show-meta input[name="_rbm_radio_show_live"]').on("change",function(o){e(this).prop("checked")?e("#gscr-live-radio-show").removeClass("hidden"):e("#gscr-live-radio-show").addClass("hidden")}))})}(jQuery);
//# sourceMappingURL=admin.js.map

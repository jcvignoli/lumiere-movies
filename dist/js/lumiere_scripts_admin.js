function GereControle(e,t){e=document.getElementById(e);return e.disabled="1"==t,!0}function checkAll(e){for(i=0;i<e.length;i++)e[i].checked=!0}function uncheckAll(e){for(i=0;i<e.length;i++)e[i].checked=!1}document.addEventListener("DOMContentLoaded",function(){jQuery("input[data-modificator]").change(function(){jQuery(this).is(":checked")&&GereControle(jQuery(this).closest("input").data("field_to_change"),jQuery(this).closest("input").data("field_to_change_value"))}),jQuery("input[data-modificator2]").change(function(){jQuery(this).is(":checked")&&GereControle(jQuery(this).closest("input").data("field_to_change2"),jQuery(this).closest("input").data("field_to_change_value2"))}),jQuery("input[data-modificator3]").change(function(){jQuery(this).is(":checked")&&GereControle(jQuery(this).closest("input").data("field_to_change3"),jQuery(this).closest("input").data("field_to_change_value3"))}),jQuery("input[data-valuemodificator]").click(function(){var e=jQuery(this).closest("input").data("valuemodificator_field");jQuery(this).is(":checked")?document.getElementById(e).value=jQuery(this).val():document.getElementById(e).value=jQuery(this).closest("input").data("valuemodificator_default")}),jQuery("input[data-checkbox_activate]").change(function(){var e=jQuery(this).closest("input").data("checkbox_activate");jQuery("#"+e).toggle(jQuery(this).closest("input").is(":checked"))}),jQuery("input[data-checkbox_activate]").trigger("change")}),document.addEventListener("DOMContentLoaded",function(){jQuery("#movemovieup").click(function(){var e=jQuery("#imdbwidgetorderContainer option:selected");e.is(":first-child")?e.insertAfter(jQuery("#imdbwidgetorderContainer option:last-child")):e.insertBefore(e.prev())}),jQuery("#movemoviedown").click(function(){var e=jQuery("#imdbwidgetorderContainer option:selected");e.is(":last-child")?e.insertBefore(jQuery("#imdbwidgetorderContainer option:first-child")):e.insertAfter(e.next())}),jQuery("#imdbconfig_save").submit(function(){jQuery("#imdbwidgetorderContainer").find("option").prop("selected",!0)})}),function(t){t(document).on("click","[data-confirm]",function(e){confirm(t(this).data("confirm"))||(e.stopImmediatePropagation(),e.preventDefault())})}(jQuery),jQuery(document).on("click","input[data-check-movies]",function(e){checkAll(document.getElementsByName("imdb_cachedeletefor_movies[]"))}),jQuery(document).on("click","input[data-check-people]",function(e){checkAll(document.getElementsByName("imdb_cachedeletefor_people[]"))}),jQuery(document).on("click","input[data-uncheck-movies]",function(e){uncheckAll(document.getElementsByName("imdb_cachedeletefor_movies[]"))}),jQuery(document).on("click","input[data-uncheck-people]",function(e){uncheckAll(document.getElementsByName("imdb_cachedeletefor_people[]"))}),jQuery(document).ready(function(e){jQuery(".if-js-closed")&&(jQuery(".if-js-closed").removeClass("if-js-closed").addClass("closed"),postboxes.add_postbox_toggles("imdblt_help"))}),document.addEventListener("DOMContentLoaded",function(){jQuery("a[data-gutenberg]").click(function(){var e=lumiere_admin_vars.wordpress_admin_path+lumiere_admin_vars.gutenberg_search_url_string;return hs.htmlExpand(this,{allowWidthReduction:!0,objectType:"iframe",width:tmppopupLarg,headingEval:"this.a.innerHTML",wrapperClassName:"titlebar",src:e})})}),document.addEventListener("DOMContentLoaded",function(){jQuery("a[data-lumiere_admin_popup]").click(function(){var e=lumiere_admin_vars.wordpress_admin_path+lumiere_admin_vars.gutenberg_search_url_string;window.open(e,"popup","resizable=yes, toolbar=no, scrollbars=yes, location=no, width=540, height=350, top=5, left=5")})});
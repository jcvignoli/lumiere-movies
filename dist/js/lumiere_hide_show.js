jQuery(".activatehidesection").click(function(){jQuery(this).next().slideToggle()}).next().hide(),document.addEventListener("DOMContentLoaded",function(){1==jQuery("input.activatehidesectionAdd").prop("checked")?jQuery(".hidesectionOfCheckbox").show():1==jQuery(".activatehidesectionRemove").prop("checked")&&jQuery(".hidesectionOfCheckbox").hide(),jQuery("input.activatehidesectionAdd").click(function(){jQuery(".hidesectionOfCheckbox").slideToggle()}).nextAll(".hidesectionOfCheckbox").show(),1==jQuery("input.activatehidesectionAddTwo").prop("checked")?jQuery(".hidesectionOfCheckboxTwo").show():1==jQuery(".activatehidesectionRemoveTwo").prop("checked")&&jQuery(".hidesectionOfCheckboxTwo").hide(),jQuery("input.activatehidesectionAddTwo").click(function(){jQuery(".hidesectionOfCheckboxTwo").slideToggle()}).nextAll(".hidesectionOfCheckboxTwo").show()});
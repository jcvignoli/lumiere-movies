!function(e,i,t,a){var l=t.createElement,s=t.RawHTML,{registerBlockType:t}=e,e=l("svg",{width:35,height:35,viewBox:"0 0 200 200"},l("path",{d:"M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"}));t("lumiere/widget",{title:a.__("Lumière! Widget","lumiere-movies"),description:a.__("Lumière Widget adds movies to your widgets sidebar and enhance your posts about cinema.","lumiere-movies"),icon:e,category:"widgets",keywords:["widget","lumiere","imdb","movie","film"],attributes:{lumiere_input:{type:"string",options:"html",default:"Lumière Movies"}},edit:function(i){return l("div",{className:(i.className,"lumiere_block_widget"),tagName:"div"},l("img",{className:(i.className,"lumiere_block_widget_image"),src:lumiere_admin_vars.imdb_path+"pics/lumiere-ico80x80.png"}),s({className:(i.className,"lumiere_block_widget_title"),children:"Lumière! Widget"}),s({className:(i.className,"lumiere_block_widget_explanation"),tagName:"gutenberg",children:a.__("This widget will display movies in your articles.","lumiere-movies")+"<br />"+a.__("When editing a post or a page, simply add a movie title or id using the Lumière tool in your sidebar to show a movie.","lumiere-movies")+"<br />"}),l("div",{className:(i.className,"lumiere_block_widget_container"),tagName:"div"},l("div",{className:(i.className,"lumiere_block_widget_entertitle"),tagName:"div",children:"Enter widget title:",onChange:function(e){i.setAttributes({lumiere_input:e.target.value})}}),l("div",{className:(i.className,"lumiere_block_widget_enterinput"),tagName:"div"},l("input",{value:i.attributes.lumiere_input,className:(i.className,"lumiere_block_widget_input"),tagName:"input",onChange:function(e){i.setAttributes({lumiere_input:e.target.value})}}))))},save:function(e){return l("div",{className:e.className},l(i.RichText.Content,{className:"lumiere_block_widget_input",value:"[lumiereWidget]"+e.attributes.lumiere_input+"[/lumiereWidget]"}))}})}(window.wp.blocks,window.wp.blockEditor,window.wp.element,(window.wp.components,window.wp.data,window.wp.i18n));
!function(e,i,t,l){var s=t.createElement,o=t.RawHTML,{registerBlockType:a}=e,t=l.__("Enter the name or the IMDb ID movie","lumiere-movies"),e=s("svg",{width:35,height:35,viewBox:"0 0 200 200"},s("path",{d:"M10 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 170 c0 -5 23 -10 50 -10 28 0 50 5 50 10 0 6 -22 10 -50 10 -27 0 -50 -4 -50 -10zM170 100 c0 -47 4 -80 10 -80 6 0 10 33 10 80 0 47 -4 80 -10 80 -6 0 -10 -33 -10 -80zM50 110 c0 -29 1 -30 50 -30 49 0 50 1 50 30 0 29 -1 30 -50 30 -49 0 -50 -1 -50 -30zM50 35 c0 -11 12 -15 50 -15 38 0 50 4 50 15 0 11 -12 15 -50 15 -38 0 -50 -4 -50 -15z"}));a("lumiere/main",{title:l.__("Lumière: movie inside a post","lumiere-movies"),description:l.__("Insert a series of details related to a movie in your post.","lumiere-movies"),icon:e,category:"embed",keywords:["embed","lumiere","imdb","movies","film"],attributes:{lumiere_imdblt_select:{type:"string",options:"html",default:"imdblt"},content:{type:"string",default:t}},edit:function(t){return s("div",{className:(t.className,"lumiere_gutenberg_block_intothepost"),tagName:"div"},s("img",{className:(t.className,"lumiere_gutenberg_block_intothepost-image"),src:lumiere_admin_vars.imdb_path+"pics/lumiere-ico-noir80x80.png"}),o({className:(t.className,"lumiere_gutenberg_block_intothepost-title"),children:"Lumière! movies"}),o({className:(t.className,"lumiere_gutenberg_block_intothepost-explanation"),tagName:"gutenberg",children:l.__("Use this block to retrieve movie or people information from the IMDb and insert in your post.","lumiere-movies")+"<br />"+l.__("You can also click on this link to get the","lumiere-movies")+' <a data-lumiere_admin_popup="yes" onclick="window.open(\''+lumiere_admin_vars.wordpress_admin_path+"lumiere/search/', '_blank', 'location=yes,height=400,width=500,scrollbars=yes,status=yes');\" class=\"linkincmovie link-imdblt-highslidepeople highslide\" target=\"_blank\">"+l.__("IMDb movie id","lumiere-movies")+"</a> "+l.__("and insert it.","lumiere-movies")}),s("div",{className:(t.className,"lumiere_gutenberg_block_intothepost-container"),tagName:"div"},s("div",{className:(t.className,"lumiere_gutenberg_block_intothepost-select"),tagName:"div"},s("select",{value:t.attributes.lumiere_imdblt_select,onChange:function(e){t.setAttributes({content:""}),t.setAttributes({lumiere_imdblt_select:e.target.value})}},s("option",{value:"imdblt",label:l.__("By movie title","lumiere-movies")}),s("option",{value:"imdbltid",label:l.__("By IMDb ID","lumiere-movies")}))),s(i.RichText,{tagName:"div",className:"lumiere_gutenberg_block_intothepost-content",value:t.attributes.content,onChange:function(e){t.setAttributes({content:e})}})))},save:function(e){return s("div",{className:e.className},s(i.RichText.Content,{className:"lumiere_gutenberg_block_intothepost-content",value:"["+e.attributes.lumiere_imdblt_select+"]"+e.attributes.content+"[/"+e.attributes.lumiere_imdblt_select+"]"}))}})}(window.wp.blocks,window.wp.blockEditor,window.wp.element,(window.wp.components,window.wp.data,window.wp.i18n));
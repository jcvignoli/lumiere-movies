/* Supposed to be used for legacy widgets and create a new block */

export const settings = {
	title: 'Lumière Widget',
	description: 'Lumière Widget adds movies to your posts',
	transforms: {
	    from: [
		 {
		     type: 'block',
		     blocks: [ 'core/legacy-widget' ],
		     isMatch: ( { idBase, instance } ) => {
			  if ( ! instance?.raw ) {
			      // Can't transform if raw instance is not shown in REST API.
			      return false;
			  }
			  return idBase === 'lumiere_movies_widget';
		     },
		     transform: ( { instance } ) => {
			  return createBlock( 'lumiere/widget', {
			      name: instance.raw.name,
			  } );
		     },
		 },
	    ]
	},
};

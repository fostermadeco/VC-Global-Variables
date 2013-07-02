VC Global Variables
===================

This is an ExpressionEngine extension that creates global variables from channels and fields which outputs the channel/field id.

This is useful especially when writing queries using the Query module.  Imagine there is a channel named `blog` and a field named `blog_location`.  Normally, you would hardcode the field id and the channel id in the query.

	{exp:query sql="SELECT field_id_1 AS blog_location FROM exp_channel_data WHERE channel_id = 1"}
		{blog_location}
	{/exp:query}

In the next example, we will use the global variables for field id and channel channel_id

	{exp:query sql="SELECT {field_blog_location} AS blog_location FROM exp_channel_data WHERE channel_id = {channel_blog}"}
		{blog_location}
	{/exp:query}

Another example would be for use in a module or fieldtype.  This is not a good solution for distributable add-ons, but if a custom add-on is being written for a client, it is a good way to keep from having to always use the channel or field id.

	$blog_location = $this->EE->config->_global_vars['field_blog_location'];
	$blog_channel_id = $this->EE->config->_global_vars['channel_blog'];
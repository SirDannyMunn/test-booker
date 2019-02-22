<template>
<div>
	<p style="line-height: 2;">
		{{ datetime }}
		<span><i class=""></i></span>
		<a class="btn btn-outline-primary float-right">Book</a>
	</p>
</div>
</template>

<script>
export default {
	props: {
		datetime: ""
	},
	data() {
		return {
			buttonVisible: false,
		};
	},
	mounted() {
	},
	methods: {
	}
};
</script>

<style scoped>
</style>

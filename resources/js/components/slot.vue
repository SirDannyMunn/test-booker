<template>
<div>

	<small v-if="place">Your are in <strong>{{ place }} Place</strong> for this slot</small>

	<p style="line-height: 2;">
		{{ datetime }}
		<span><i class=""></i></span>
		<button v-if="!buttonDisabled" @click="$root.$emit('slotButtonClicked', $props)" class="btn btn-outline-primary float-right">Book</button>
		<button v-else class="btn btn-outline-primary float-right">....</button>
	</p>
</div>
</template>

<script>
export default {
	props: {
		datetime: "",
		place: "",
		slotId: "",
	},
	data() {
		return {
			buttonDisabled: false,
		};
	},
	mounted() {
		this.$root.$on('slotBooked', data => {
			if (data.slotId==this.slotId) {
				this.buttonDisabled=true;
				this.place="1st";
			}
		});
	}
};
</script>

<style scoped>
</style>

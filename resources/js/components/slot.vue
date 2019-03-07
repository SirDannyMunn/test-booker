<template>
<div class="row">

	<small v-if="place">Your are in <strong>{{ place }} Place</strong> for this slot</small>

	<div class="col-9">
		<p style="line-height: 2;">
			{{ datetime }}
		</p>
	</div>

	<div class="col-3">
		<button v-if="!buttonDisabled" @click="$root.$emit('slotButtonClicked', $props)" class="btn btn-outline-primary float-right">Book</button>
		<button v-else class="btn btn-outline-primary float-right">....</button>
	</div>
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

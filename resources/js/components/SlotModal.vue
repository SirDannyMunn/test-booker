<template>
<!-- Modal -->
<div ref="slotModal" class="modal" id="slotModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:unset;">
                <h2 class="col-12 modal-title text-center" id="exampleModalLongTitle">
                    <div style="width: 45px; height:50px" class="float-left"></div>
                        Book slot?
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </h2>

            </div>
            <div class="modal-body slot-modal text-center">

				<div class="row">
					<div class="col">
						{{ this.datetime }}
					</div>	
				</div>	

				<div class="row">
					<div class="col">
						You can book this slot and if the person currently being offered it turns it down then we will book it for you.	
					</div>	
				</div>	
				
				<div class="row mt-5">
					<div class="col">
						<button class="btn btn-lg btn-primary" @click="promotePoints">Book</button>
					</div>
				</div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
	data() {
		return {
			datetime: "",
			place: "",
			slotId: "",
		};
	},
	mounted() {
		this.$root.$on('slotButtonClicked', data => {
			$(this.$refs.slotModal).modal('show');

			this.datatime = data.datetime;
			this.place = data.place;
			this.slotId = data.slotId;
		});
	},
	methods: {
		promotePoints() {
			window.axios.get(`slot/${this.slotId}/promote`).then((result) => {
				console.log(result);

				$(this.$refs.slotModal).modal('hide');
				
				this.$root.$emit('slotBooked', {slotId: this.slotId});
			}).catch((err) => {

			});
		}
	}
};
</script>

<style scoped>
</style>

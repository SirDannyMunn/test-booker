<template>
<div>
    <form action="/charge" method="post" id="payment-form">
        <div class="form-row">
            
            <!-- <label for="card-element">
                Credit or debit card
            </label> -->
            <div id="card-element" class="form-control">
                <!-- A Stripe Element will be inserted here. -->
            </div>

            <!-- Used to display Element errors. -->
            <div id="card-errors" role="alert"></div>
        </div>

        <!-- <button id="card-button" :data-secret="clientSecret">Submit Payment</button> -->
    </form>
</div>
</template>

<script>
export default {
	props: {
		clientSecret: ""
	},
	data() {
		return {
			stripe: null,
			elements: null
		};
	},
	mounted() {
		const stripe = Stripe("pk_test_vS7SwHJ4n7nnrV853UCMzzbK", {
			betas: ["payment_intent_beta_3"]
		});

		const options = {
			locale: "en"
		};

		const elements = stripe.elements({ options });

		// Custom styling can be passed to options when creating an Element.
		const style = {
			base: {
				color: "#32325d",
				lineHeight: "18px",
				fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
				fontSmoothing: "antialiased",
				fontSize: "16px",
				"::placeholder": {
					color: "#aab7c4"
				}
			},
			invalid: {
				color: "#fa755a",
				iconColor: "#fa755a"
			}
		};

		// Create an instance of the card Element.
		const card = elements.create("card", { style });

		// Add an instance of the card Element into the `card-element` <div>.
		card.mount("#card-element");

		const cardholderName = document.getElementById("cardholder-name");
		const cardButton = document.getElementById("card-button");
		const clientSecret = cardButton.dataset.secret;

		cardButton.addEventListener("click", async ev => {
			const { paymentIntent, error } = await stripe.handleCardPayment(
				clientSecret,
				cardElement,
				{
					source_data: {
						owner: { name: cardholderName.value }
					}
				}
			);

			if (error) {
				// Display error.message in your UI.
			} else {
				// The payment has succeeded. Display a success message.
			}
		});
	},
	methods: {
		// Create a token or display an error when the form is submitted.
		createToken($e) {}
	}
};
</script>

<style scoped>
#cardholder-name {
	width: 100%;
}

#card-element {
	width: 100%;
}

.StripeElement {
	background-color: white;
	height: 40px;
	padding: 10px 12px;
	border-radius: 4px;
	border: 1px solid transparent;
	box-shadow: 0 1px 3px 0 #e6ebf1;
	-webkit-transition: box-shadow 150ms ease;
	transition: box-shadow 150ms ease;
}

.InputElement {
    display: block;
    width: 100%;
    height: calc(2.25rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 10px;
    -webkit-transition: border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
    transition: border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
    -o-transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
}

.StripeElement--focus {
	box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
	border-color: #fa755a;
}

.StripeElement--webkit-autofill {
	background-color: #fefde5 !important;
}
</style>

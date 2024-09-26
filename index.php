<?php
    // Step 1: Require the library from your Composer vendor folder
    require_once 'vendor/autoload.php';

    use MercadoPago\Client\Common\RequestOptions;
    use MercadoPago\Client\Payment\PaymentClient;
    use MercadoPago\Exceptions\MPApiException;
    use MercadoPago\MercadoPagoConfig;

    // Step 2: Set production or sandbox access token
    MercadoPagoConfig::setAccessToken("TEST-1935091980734919-092313-fb425d565ca6bfba87ca53cc21b75e8c-229579824");
    // Optional: Set runtime environment to LOCAL for testing
    MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

    // Step 3: Initialize the API client
    $client = new PaymentClient();

    try {
        // Step 4: Create the request array
        $request = [
            "transaction_amount" => 100,
            "token" => "",
            "description" => "description",
            "installments" => 1,
            "payment_method_id" => "visa",
            "payer" => [
                "email" => "user@test.com",
            ]
        ];

        // Step 5: Generate a unique X-Idempotency-Key
        $idempotencyKey = uniqid('mp_', true);

        // Step 6: Create the request options, setting the unique X-Idempotency-Key
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: $idempotencyKey"]);

        // Step 7: Make the request
        $payment = $client->create($request, $request_options);
        echo $payment->id;

    // Step 8: Handle exceptions
    } catch (MPApiException $e) {
        echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
        echo "Content: ";
        var_dump($e->getApiResponse()->getContent());
        echo "\n";
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <title>Document</title>
  </head>
  <body>
    <style>
      .card-product-container {
        margin: 5vh auto;
      }
      .card-product {
        text-align: center;
        display: flex;
        flex-direction: row;
        justify-content: space-around;
        flex-wrap: wrap;
        margin: 5vh auto;
        font-size: 25px;
      }
      .card img {
        height: 280px;
      }
      .card-product button {
        border: none;
        outline: 0;
        padding: 10px;
        color: white;
        background-color: #1bcb7f;
        text-align: center;
        cursor: pointer;
        width: 100%;
        font-size: 15px;
      }
      .card-product button:hover {
        opacity: 0.7;
      }
    </style>
    
    <div class="card-product-container">
      <div class="card-product">
        <div class="card">
          <img
            src="https://res.cloudinary.com/pabcode/image/upload/v1699871193/e-commerce/mopgcvdiepr8axkazmcp.png"
            alt="bananita-pic"
          />
          <h3 class="name">BANANITA</h3>
          <p class="price">100 $</p>

          <button id="checkout-btn">Comprar</button>
          <div id="wallet_container"></div>
        </div>
      </div>
    </div>
    <form id="form-checkout">
  <input type="text" id="form-checkout__cardholderName" placeholder="Titular de la tarjeta" />
  <input type="email" id="form-checkout__cardholderEmail" placeholder="E-mail" />
  <input type="text" id="form-checkout__cardNumber" placeholder="Número de la tarjeta" />
  <input type="text" id="form-checkout__expirationDate" placeholder="MM/AA" />
  <input type="text" id="form-checkout__securityCode" placeholder="Código de seguridad" />
  <button id="checkout-btn" type="button">Pagar</button>
</form>

<div id="wallet_container"></div>

<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
  const mp = new MercadoPago("TEST-830121ba-ef1a-40bb-a8f4-f5f5d035e728", { locale: "es-AR" });

  const cardForm = mp.cardForm({
    amount: 100,
    autoMount: true,
    form: {
      id: "form-checkout",
      cardholderName: { id: "form-checkout__cardholderName", placeholder: "Titular de la tarjeta" },
      cardholderEmail: { id: "form-checkout__cardholderEmail", placeholder: "E-mail" },
      cardNumber: { id: "form-checkout__cardNumber", placeholder: "Número de la tarjeta" },
      expirationDate: { id: "form-checkout__expirationDate", placeholder: "MM/AA" },
      securityCode: { id: "form-checkout__securityCode", placeholder: "Código de seguridad" }
    },
    callbacks: {
      onFormMounted: error => {
        if (error) console.warn("Form Mounted handling error: ", error);
      },
      onSubmit: async event => {
        event.preventDefault();

        const { token, error } = await cardForm.createCardToken();
        if (error) {
          console.error("Token generation error:", error);
          return;
        }

        // Ahora envía el token al backend
        const orderData = {
          token: token.id, // Incluye el token en la petición
          description: "Compra de ejemplo",
          transaction_amount: 100,
          installments: 1,
          payment_method_id: "visa",
          payer: {
            email: document.querySelector("#form-checkout__cardholderEmail").value
          }
        };

        try {
          const response = await fetch("http://localhost:3000/create_payment", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(orderData)
          });

          const result = await response.json();
          if (result.error) {
            console.error("Error al procesar el pago:", result.error);
          } else {
            console.log("Pago exitoso:", result);
          }
        } catch (error) {
          console.error("Error en el procesamiento:", error);
        }
      },
      onFetching: (resource) => {
        console.log("Fetching resource: ", resource);
      }
    }
  });

  document.getElementById("checkout-btn").addEventListener("click", () => {
    cardForm.submit(); // Enviar el formulario y generar el token de la tarjeta
  });
</script>

    <script>

const mp = new MercadoPago("TEST-830121ba-ef1a-40bb-a8f4-f5f5d035e728", {
  locale: "es-AR",
});

// Crear el token de la tarjeta
async function createCardToken() {
  const cardForm = mp.cardForm({
    amount: 100,
    autoMount: true,
    form: {
      id: "form-checkout",
      cardholderName: {
        id: "form-checkout__cardholderName",
        placeholder: "Titular de la tarjeta",
      },
      cardholderEmail: {
        id: "form-checkout__cardholderEmail",
        placeholder: "E-mail",
      },
      cardNumber: {
        id: "form-checkout__cardNumber",
        placeholder: "Número de la tarjeta",
      },
      expirationDate: {
        id: "form-checkout__expirationDate",
        placeholder: "MM/AA",
      },
      securityCode: {
        id: "form-checkout__securityCode",
        placeholder: "Código de seguridad",
      },
    },
  });

  const token = await cardForm.createCardToken();
  return token;
}

document.getElementById("checkout-btn").addEventListener("click", async () => {
  try {
    const cardToken = await createCardToken();

    const orderData = {
      title: document.querySelector(".name").innerText,
      quantity: 1,
      price: 100,
      token: cardToken.id // Incluir el token de la tarjeta en los datos del pedido
    };

    const response = await fetch("http://localhost:3000/create_preference", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(orderData),
    });

    const preference = await response.json();
    createCheckoutButton(preference.id);
  } catch (error) {
    alert("Error al procesar el pago :(");
  }
});
    </script>
  </body>
</html>

document.getElementById("payBtn").addEventListener("click", async () => {
  try {
    // Step 1: Get or refresh token automatically
    const tokenRes = await fetch("get_token.php");
    const tokenData = await tokenRes.json();
    const id_token = tokenData.id_token;

    // Step 2: Create payment
    const createRes = await fetch("create_payment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_token }),
    });
    const createData = await createRes.json();

    if (createData.bkashURL) {
      window.location.href = createData.bkashURL; // Redirect to bKash
    } else {
      alert("Error creating payment: " + JSON.stringify(createData));
    }
  } catch (err) {
    alert("Error: " + err);
  }
});

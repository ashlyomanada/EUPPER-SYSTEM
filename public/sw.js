self.addEventListener("push", (event) => {
  console.log("Push event received:", event);

  const data = event.data
    ? event.data.json()
    : { title: "Default Title", body: "Default Body" };
  console.log("Notification data:", data);

  self.registration.showNotification(data.title, {
    body: data.body,
  });
});

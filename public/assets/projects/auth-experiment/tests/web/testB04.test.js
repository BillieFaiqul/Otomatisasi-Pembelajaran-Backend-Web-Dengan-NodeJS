const fs = require("fs");
const bcrypt = require("bcryptjs");
const puppeteer = require("puppeteer");
const mongoose = require("mongoose");
const { toMatchImageSnapshot } = require("jest-image-snapshot");
expect.extend({ toMatchImageSnapshot });

require("dotenv").config();
const options = {
  showPrefix: false,
  showMatcherMessage: true,
  showStack: true,
};

let browser;
let page;

const user = {
  name: "John Doe",
  username: "johndoe",
  email: "johndoe@gmail.com",
  password: "12345678",
};

beforeAll(async () => {
  mongoose.set("strictQuery", true);
  await mongoose.connect(process.env.MONGODB_URI, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
  await mongoose.connection
    .collection("users")
    .findOneAndDelete({ username: user.username });

  user.password = bcrypt.hashSync(user.password, bcrypt.genSaltSync(10));
  await mongoose.connection.collection("users").insertOne(user);
  user.password = "12345678";

  browser = await puppeteer.launch({
    headless: true,
    slowMo: 0,
    devtools: false,
    defaultViewport: {
      width: 1024,
      height: 768,
    },
  });
  page = await browser.newPage();
  await page.setDefaultTimeout(10000);
  await page.setDefaultNavigationTimeout(20000);
});

beforeEach(async () => {
  await page.goto(`http://localhost:${process.env.PORT}/login`);
});

afterAll(async () => {
  await mongoose.connection
    .collection("users")
    .findOneAndDelete({ username: user.username });
  await mongoose.connection.close();
  await browser.close();
});

describe("Testing the edit page", () => {
  it("should login a user", async () => {
    await page.type("#username", user.username);
    await page.type("#password", user.password);
    await page.click("button");
    await new Promise((resolve) => setTimeout(resolve, 1000));

    const url = await page.url();
    expect(
      url,
      `The user should be redirected to the profile page after logging in, the current url is "${url}"`,
      options
    ).toBe(`http://localhost:${process.env.PORT}/profile`);
  });

  it("should have the name, username, email inputs in the edit page", async () => {
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(1)");
    const url = await page.url();
    expect(
      url,
      `The user should be redirected to the edit page after clicking the edit button, the current url is "${url}"`,
      options
    ).toBe(`http://localhost:${process.env.PORT}/profile/update`);

    const nameInput = await page.$eval("#name", (el) => ({
      type: el.type,
      value: el.value,
    }));
    const usernameInput = await page.$eval("#username", (el) => ({
      type: el.type,
      value: el.value,
    }));
    const emailInput = await page.$eval("#email", (el) => ({
      type: el.type,
      value: el.value,
    }));
    expect(
      nameInput,
      `The name input should be present in the edit page, it should be of type "text" and the value should be same to the user's data`,
      options
    ).toEqual({
      type: "text",
      value: user.name,
    });
    expect(
      usernameInput,
      `The username input should be present in the edit page, it should be of type "text" and the value should be same to the user's data`,
      options
    ).toEqual({
      type: "text",
      value: user.username,
    });
    expect(
      emailInput,
      `The email input should be present in the edit page, it should be of type "email" and the value should be same to the user's data`,
      options
    ).toEqual({
      type: "email",
      value: user.email,
    });
  });

  it("should not update the user's data if the inputs are empty", async () => {
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(1)");
    let nameInput = await page.$("#name");
    await nameInput.click({ clickCount: 3 });
    await nameInput.press("Backspace");

    await page.click("button");
    await new Promise((resolve) => setTimeout(resolve, 1000));

    const message = await page.$eval(".message", (el) => el.textContent);

    expect(
      message,
      `The user should not be able to update the data if the inputs are empty, the message should be "Please fill out the following required field(s): name"`,
      options
    ).toBe("Please fill out the following required field(s): name");
  });

  it("should update the user's data", async () => {
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(1)");
    let nameInput = await page.$("#name");
    await nameInput.click({ clickCount: 3 });
    await nameInput.press("Backspace");
    await nameInput.type("John Doe updated");
    await page.click("button");
    await new Promise((resolve) => setTimeout(resolve, 1000));

    const newname = await page.$eval("#name", (el) => el.value);
    expect(
      newname,
      `The user should be able to update the data, the name should be updated to after submitting the form`,
      options
    ).toBe("John Doe updated");
  });

  it("should have three inputs for the password", async () => {
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(2)");
    const url = await page.url();
    expect(
      url,
      `The user should be redirected to the password page after clicking the password button, the current url is "${url}"`,
      options
    ).toBe(`http://localhost:${process.env.PORT}/profile/update/password`);

    const passwordInput = await page.$eval("#newPassword", (el) => ({
      type: el.type,
      name: el.name,
    }));
    const passwordConfirmInput = await page.$eval(
      "#confirmNewPassword",
      (el) => ({
        type: el.type,
        name: el.name,
      })
    );
    const passwordCurrentInput = await page.$eval("#currentPassword", (el) => ({
      type: el.type,
      name: el.name,
    }));
    expect(
      passwordInput,
      `The new password input should be present in the password page, it should be of type "password" and the name should be "newPassword"`,
      options
    ).toEqual({
      type: "password",
      name: "newPassword",
    });
    expect(
      passwordConfirmInput,
      `The confirm new password input should be present in the password page, it should be of type "password" and the name should be "confirmNewPassword"`,
      options
    ).toEqual({
      type: "password",
      name: "confirmNewPassword",
    });
    expect(
      passwordCurrentInput,
      `The current password input should be present in the password page, it should be of type "password" and the name should be "currentPassword"`,
      options
    ).toEqual({
      type: "password",
      name: "currentPassword",
    });
  });

  it("should update the user's password", async () => {
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(2)");
    let newPasswordInput = await page.$("#newPassword");
    await newPasswordInput.type("123456789");

    let confirmNewPasswordInput = await page.$("#confirmNewPassword");
    await confirmNewPasswordInput.type("123456789");

    let currentPasswordInput = await page.$("#currentPassword");
    await currentPasswordInput.type("12345678");

    await page.click("button");
    await new Promise((resolve) => setTimeout(resolve, 1000));

    const message = await page.$eval(".message", (el) => el.textContent);
    expect(
      message,
      `The user message after updating the password should be "Password Updated Successfully"`,
      options
    ).toBe("Password Updated Successfully");
  });

  it("should not update the user's password if the current password is wrong", async () => {
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(2)");
    let newPasswordInput = await page.$("#newPassword");
    await newPasswordInput.type("123456789");
    let confirmNewPasswordInput = await page.$("#confirmNewPassword");
    await confirmNewPasswordInput.type("123456789");
    let currentPasswordInput = await page.$("#currentPassword");
    await currentPasswordInput.type("12345678");

    await page.click("button");
    await new Promise((resolve) => setTimeout(resolve, 1000));

    const message = await page.$eval(".message", (el) => el.textContent);

    expect(
      message,
      `If the current password is wrong the user message after updating the password should be "Incorrect Password"`,
      options
    ).toBe("Incorrect Password");
  });
});

describe("Testing the edit page image snapshots", () => {
  it("matches the expected styling for the edit page", async () => {
    if (!fs.existsSync("tests/web/images/edit-page.png")) {
      throw new Error(
        `The reference image for the edit page does not exist, please import the image from the "tests/web/images/edit-page.png"`
      );
    }
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(1)");
    const screenshot = await page.screenshot({ fullPage: true });
    expect(
      screenshot,
      `The web styling for the edit page is not correct check the file "tests/web/images/__diff_output__/edit-page-diff.png" to find the difference`,
      options
    ).toMatchImageSnapshot({
      customDiffConfig: { threshold: 0.9 },
      customSnapshotsDir: "tests/web/images",
      customSnapshotIdentifier: "edit-page",
    });
  });

  it("matches the expected styling for the edit password page", async () => {
    if (!fs.existsSync("tests/web/images/edit-password-page.png")) {
      throw new Error(
        `The reference image for the edit password page does not exist, please import the image from the "tests/web/images/edit-password-page.png"`
      );
    }
    await page.goto(`http://localhost:${process.env.PORT}/profile`);
    await page.click(".action > a:nth-child(2)");
    const screenshot = await page.screenshot({ fullPage: true });
    expect(
      screenshot,
      `The web styling for the edit password page is not correct check the file "tests/web/images/__diff_output__/edit-password-page-diff.png" to find the difference`,
      options
    ).toMatchImageSnapshot({
      customDiffConfig: { threshold: 0.9 },
      customSnapshotsDir: "tests/web/images",
      customSnapshotIdentifier: "edit-password-page",
    });
  });
});

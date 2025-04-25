const mongoose = require("mongoose");
const request = require("supertest");
const app = require("../../app");
const packages = require("../../package.json");

require("dotenv").config();
mongoose.set("strictQuery", true);

const options = {
  showPrefix: false,
  showMatcherMessage: true,
  showStack: true,
};

beforeAll(async () => {
  await connectDB().then(
    async () => {
      console.log("Database connected successfully");
    },
    (err) => {
      console.log("There is problem while connecting database " + err);
    }
  );
});

describe("Testing application configuration", () => {
  // Testing the package.json file for the necessary development packages
  // the packages are cross-env, jest, nodemon, supertest, jest-image-snapshot, jest-expect-message, puppeteer
  it("Should have the necessary development packages", (done) => {
    expect(
      packages.devDependencies,
      `The package "cross-env" was not found in the devDependencies object. Install the package by running this command "npm i cross-env --save-dev"`,
      options
    ).toHaveProperty("cross-env");
    expect(
      packages.devDependencies,
      `The package "jest" was not found in the devDependencies object. Install the package by running this command "npm i jest --save-dev"`,
      options
    ).toHaveProperty("jest");
    expect(
      packages.devDependencies,
      `The package "nodemon" was not found in the devDependencies object. Install the package by running this command "npm i nodemon --save-dev"`,
      options
    ).toHaveProperty("nodemon");
    expect(
      packages.devDependencies,
      `The package "supertest" was not found in the devDependencies object. Install the package by running this command "npm i supertest --save-dev"`,
      options
    ).toHaveProperty("supertest");
    expect(
      packages.devDependencies,
      `The package "jest-image-snapshot" was not found in the devDependencies object. Install the package by running this command "npm i jest-image-snapshot --save-dev"`,
      options
    ).toHaveProperty("jest-image-snapshot");
    expect(
      packages.devDependencies,
      `The package "jest-expect-message" was not found in the devDependencies object. Install the package by running this command "npm i jest-expect-message --save-dev"`,
      options
    ).toHaveProperty("jest-expect-message");
    expect(
      packages.devDependencies,
      `The package "puppeteer" was not found in the devDependencies object. Install the package by running this command "npm i puppeteer --save-dev"`,
      options
    ).toHaveProperty("puppeteer");
    done();
  });
  // Testing the package.json file for the necessary production packages
  // the packages are dotenv, ejs, express, express-ejs-layouts, mongoose, mongoose-slug-generator
  it("should have the necessary production packages", (done) => {
    expect(
      packages.dependencies,
      `The package "dotenv" was not found in the dependencies object. Install the package by running this command "npm i dotenv --save"`,
      options
    ).toHaveProperty("dotenv");
    expect(
      packages.dependencies,
      `The package "ejs" was not found in the dependencies object. Install the package by running this command "npm i ejs --save"`,
      options
    ).toHaveProperty("ejs");
    expect(
      packages.dependencies,
      `The package "express" was not found in the dependencies object. Install the package by running this command "npm i express --save"`,
      options
    ).toHaveProperty("express");
    expect(
      packages.dependencies,
      `The package "express-ejs-layouts" was not found in the dependencies object. Install the package by running this command "npm i express-ejs-layouts --save"`,
      options
    ).toHaveProperty("express-ejs-layouts");
    expect(
      packages.dependencies,
      `The package "mongoose" was not found in the dependencies object. Install the package by running this command "npm i mongoose --save"`,
      options
    ).toHaveProperty("mongoose");
    expect(
      packages.dependencies,
      `The package "mongoose-slug-generator" was not found in the dependencies object. Install the package by running this command "npm i mongoose-slug-generator --save"`,
      options
    ).toHaveProperty("mongoose-slug-generator");
    done();
  });
  // Testing the application name
  // the application name should be "api-experiment"
  it("should have the right name and packages", (done) => {
    expect(
      packages.name,
      `The name provided "${packages.name}" is wrong. The application name should be "api-experiment", check the package.json file`,
      options
    ).toBe("api-experiment");
    done();
  });
  // Testing the application environment variables
  // the application should have the following environment variables
  // MONGODB_URI, MONGODB_URI_TEST, PORT
  it("should have the right environment variables", (done) => {
    expect(
      process.env,
      `The environment variable "MONGODB_URI" was not found. Check the .env file`,
      options
    ).toHaveProperty("MONGODB_URI");
    expect(
      process.env,
      `The environment variable "MONGODB_URI_TEST" was not found. Check the .env.test file`,
      options
    ).toHaveProperty("MONGODB_URI_TEST");
    expect(
      process.env.MONGODB_URI !== process.env.MONGODB_URI_TEST,
      `The environment variable "MONGODB_URI" and "MONGODB_URI_TEST" should not be the same. Check the .env`,
      options
    ).toBeTruthy();
    expect(
      process.env,
      `The environment variable "PORT" was not found. Check the .env file`,
      options
    ).toHaveProperty("PORT");
    expect(
      process.env.NODE_ENV,
      `The environment variable "NODE_ENV" was not found. Check the test script in the package.json file`
    ).toBe("test");
    done();
  });
  // Testing the application connection to the database using the test environment
  it("should have the right database connection", (done) => {
    expect(
      mongoose.connection.readyState,
      `The application is not connected to the database. Check the correctness of the MONGODB_URI_TEST variable in the .env file or the connection to the internet`,
      options
    ).toBe(1);
    done();
  });
  // Testing the application configuration
  it("should be using json format and express framework", (done) => {
    let application_stack = [];
    app._router.stack.forEach((element) => {
      application_stack.push(element.name);
    });
    expect(
      application_stack,
      `The application is not using the json format. Check the app.js file`,
      options
    ).toContain("query");
    expect(
      application_stack,
      `The application is not using the express framework. Check the app.js file`,
      options
    ).toContain("expressInit");
    expect(
      application_stack,
      `The application is not using the json format. Check the app.js file`,
      options
    ).toContain("jsonParser");
    expect(
      application_stack,
      `The application is not using the urlencoded format. Check the app.js file`,
      options
    ).toContain("urlencodedParser");
    done();
  });
});
// Testing the application testing route
describe("Testing GET /api/v1/test", () => {
  // Testing the application testing route without request
  it("should return alive", async () => {
    const res = await request(app).get("/api/v1/test");
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the GET /api/v1/test route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response should contain the property "alive", but it does not, change the response in the function that handles the GET /api/v1/test route to return {alive: 'True'}`,
      options
    ).toHaveProperty("alive");
    expect(
      res.body.alive,
      `The response should be {alive: 'True'}, but it is "${res.body.alive}", change the response in the function that handles the GET /api/v1/test route to return {alive: 'True'}`,
      options
    ).toBe("True");
    expect(
      res.req.method,
      `The request method should be GET, but it is "${res.req.method}", change the request method in the function that handles the GET /api/v1/test route`,
      options
    ).toBe("GET");
    expect(
      res.type,
      `The response type should be application/json, but it is "${res.type}", change the response type in the function that handles the GET /api/v1/test route`
    ).toBe("application/json");
  });
  // Testing the application testing route with request
  it("should return the same message", async () => {
    const res = await request(app)
      .get("/api/v1/test")
      .send({ message: "Hello" });
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the GET /api/v1/test route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response should contain the property "message", but it does not, change the response in the function that handles the GET /api/v1/test route to return {message: req.body.message}`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response should be {message: 'Hello'}, but it is "${res.body.message}", change the response in the function that handles the GET /api/v1/test route to return {message: req.body.message}`,
      options
    ).toBe("Hello");
    const res2 = await request(app)
      .get("/api/v1/test")
      .send({ message: "Hello World" });
    expect(
      res2.body.message,
      `The response should be {message: 'Hello World'}, but it is "${res2.body.message}", change the response in the function that handles the GET /api/v1/test route to return {message: req.body.message}`,
      options
    ).toBe("Hello World");
  });
});

afterAll(async () => {
  await disconnectDB();
});

async function connectDB() {
  return mongoose.connect(process.env.MONGODB_URI_TEST, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
}

async function disconnectDB() {
  await mongoose.connection.close();
}

const mongoose = require("mongoose");
const request = require("supertest");
const app = require("../../app");
const packages = require("../../package.json");
const jwt = require("jsonwebtoken");
const User = require("../../models/user.model");

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
  // the packages are bcryptjs, cookie-parser, cors, dotenv, ejs, express, express-ejs-layouts, express-unless, jsonwebtoken, mongoose
  it("should have the necessary production packages", (done) => {
    expect(
      packages.dependencies,
      `The package "bcryptjs" was not found in the dependencies object. Install the package by running this command "npm i bcryptjs --save"`,
      options
    ).toHaveProperty("bcryptjs");
    expect(
      packages.dependencies,
      `The package "cookie-parser" was not found in the dependencies object. Install the package by running this command "npm i cookie-parser --save"`,
      options
    ).toHaveProperty("cookie-parser");
    expect(
      packages.dependencies,
      `The package "cors" was not found in the dependencies object. Install the package by running this command "npm i cors --save"`,
      options
    ).toHaveProperty("cors");
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
      `The package "express-unless" was not found in the dependencies object. Install the package by running this command "npm i express-unless --save"`,
      options
    ).toHaveProperty("express-unless");
    expect(
      packages.dependencies,
      `The package "jsonwebtoken" was not found in the dependencies object. Install the package by running this command "npm i jsonwebtoken --save"`,
      options
    ).toHaveProperty("jsonwebtoken");

    expect(
      packages.dependencies,
      `The package "mongoose" was not found in the dependencies object. Install the package by running this command "npm i mongoose --save"`,
      options
    ).toHaveProperty("mongoose");

    done();
  });
  // Testing the application name
  // the application name should be "api-experiment"
  it("should have the right name and packages", (done) => {
    expect(
      packages.name,
      `The name provided "${packages.name}" is wrong. The application name should be "auth-experiment", check the package.json file`,
      options
    ).toBe("auth-experiment");
    done();
  });
  // Testing the application environment variables
  // the application should have the following environment variables
  // MONGODB_URI, MONGODB_URI_TEST, PORT JWT_SECRET, JWT_EXPIRE
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
    expect(
      process.env,
      `The environment variable "JWT_SECRET" was not found. Check the .env file`,
      options
    ).toHaveProperty("JWT_SECRET");
    expect(
      process.env,
      `The environment variable "JWT_EXPIRE" was not found. Check the .env file`,
      options
    ).toHaveProperty("JWT_EXPIRE");
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
    expect(
      application_stack,
      `The application is not using the cors middleware. Check the app.js file`,
      options
    ).toContain("corsMiddleware");
    expect(
      application_stack,
      `The application is not using the error handler middleware. Check the app.js file`,
      options
    ).toContain("errorHandler");
    done();
  });
});
// Testing the application testing route
describe("Testing GET /api/v1/test", () => {
  // Testing the application testing route without request
  it("should return message from the testing endpoint", async () => {
    const res = await request(app).get("/api/v1/test");
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the GET /api/v1/test route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response should contain the property "message", but it does not, change the response in the function that handles the GET /api/v1/test route to return {message: 'Welcome to the Auth-Experiment API'}`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response should be {message: 'Welcome to the Auth-Experiment API'}, but it is "${res.body.message}", change the response in the function that handles the GET /api/v1/test route to return {message: 'Welcome to the Auth-Experiment API'}`,
      options
    ).toBe("Welcome to the Auth-Experiment API");
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
});

describe("Testing POST /api/v1/auth/register", () => {
  // Testing the application register endpoint
  it("should register a new user", async () => {
    const user = {
      name: "testnameforuser",
      username: "testusernameforuser",
      email: "testemailfor@user.com",
      password: "testpasswordforuser",
      confirmPassword: "testpasswordforuser",
    };
    const res = await request(app).post("/api/v1/register").send(user);
    expect(
      res.statusCode,
      `The status code should be 201, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/register route`,
      options
    ).toBe(201);
    expect(
      res.body,
      `The response should contain the property "message", but it does not, change the response in the function that handles the POST /api/v1/register route to return {message: 'Registered User Successfully'}`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response should be {message: 'Registered User Successfully'}, but it is "${res.body.message}", change the response in the function that handles the POST /api/v1/register route to return {message: 'Registered User Successfully'}`,
      options
    ).toBe("Registered User Successfully");
    expect(
      res.body,
      `The response should contain the property "user", but it does not, change the response in the function that handles the POST /api/v1/register route to return the user object`,
      options
    ).toHaveProperty("user");
    expect(
      res.body,
      `The response should contain the property "token", but it does not, change the response in the function that handles the POST /api/v1/register route to return the token`,
      options
    ).toHaveProperty("token");
    // verify the token
    const decoded = jwt.verify(res.body.token, process.env.JWT_SECRET);
    expect(
      decoded,
      `The token should contain the property "id", but it does not, change the token generation in the function that handles the POST /api/v1/register route to return the user id`,
      options
    ).toHaveProperty("id");
    // verify the user
    const userFromDB = await User.findById(decoded.id);
    expect(
      userFromDB,
      `The user should be saved in the database, but it is not, check the function that handles the POST /api/v1/register route`,
      options
    ).not.toBeNull();
    expect(
      [userFromDB.name, userFromDB.username, userFromDB.email],
      `The user should be saved in the database with the correct properties, but it is not, check the function that handles the POST /api/v1/register route`,
      options
    ).toEqual([user.name, user.username, user.email]);
    // test toJSON method
    expect(
      userFromDB.toJSON(),
      `The toJSON method should return the user object without the password property, but it does not, check the toJSON method in the user model`,
      options
    ).not.toHaveProperty("password");
  });

  // Testing the application register endpoint with missing fields
  it("should return an error if the user does not provide all the required fields", async () => {
    const user = {
      name: "testnameforuser",
      username: "testusernameforuser2",
      password: "testpasswordforuser",
      confirmPassword: "testpasswordforuser",
    };
    const res = await request(app).post("/api/v1/register").send(user);
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/register route`,
      options
    ).toBe(400);
    expect(
      res.body,
      `The response should contain the property "message", but it does not, change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response should be {message: 'Please fill out the following required field(s): email'}, but it is "${res.body.message}", change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toBe("Please fill out the following required field(s): email");
  });

  // Testing the application register endpoint with invalid email
  it("should return an error if the user provides an invalid email", async () => {
    const user = {
      name: "testnameforuser",
      username: "testusernameforuser1",
      email: "testemailforuser.com",
      password: "testpasswordforuser",
      confirmPassword: "testpasswordforuser",
    };
    const res = await request(app).post("/api/v1/register").send(user);
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/register route`,
      options
    ).toBe(400);
    expect(
      res.body,
      `The response should contain the property "message", but it does not, change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response should be {message: 'Please enter a valid email'}, but it is "${res.body.message}", change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toContain("Please enter a valid email");
  });

  // Testing the application register endpoint with invalid password
  it("should return an error if the user provides an invalid password", async () => {
    const user = {
      name: "testnameforuser",
      username: "testusernameforuser3",
      email: "testuse5@email.com",
      password: "test",
      confirmPassword: "test",
    };
    const res = await request(app).post("/api/v1/register").send(user);
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}" when the password length is less than 8 characters, change the status code in the function that handles the POST /api/v1/register route`,
      options
    ).toBe(400);
    expect(
      res.body,
      `The response should contain the property "message" when the password length is less than 8 characters, but it does not, change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response should be {message: 'Password must be at least 8 characters'} when the password length is less than 8 characters, but it is "${res.body.message}", change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toBe("Password must be at least 8 characters");
  });

  // Testing the application register endpoint with invalid confirm password
  it("should return an error if the user provides an invalid confirm password", async () => {
    const user = {
      name: "testnameforuser",
      username: "testusernameforuser4",
      email: "testuse55@email.com",
      password: "testpasswordforuser",
      confirmPassword: "testpasswordforuser1",
    };
    const res = await request(app).post("/api/v1/register").send(user);
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}" when the confirm password don't match the password provided, change the status code in the function that handles the POST /api/v1/register route`,
      options
    ).toBe(400);
    expect(
      res.body,
      `The response should contain the property "message" when the confirm password don't match the password provided, but it does not, change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response should be {message: 'Passwords do not match'} when the confirm password don't match the password provided, but it is "${res.body.message}", change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toBe("Passwords do not match");
  });

  // Testing the application register endpoint with existing email
  it("should return an error if the user provides an email or username that already exists", async () => {
    const user = {
      name: "testnameforuser",
      username: "testusernameforuser",
      email: "testemailfor@user.com",
      password: "testpasswordforuser",
      confirmPassword: "testpasswordforuser",
    };
    const res = await request(app).post("/api/v1/register").send(user);
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/register route`,
      options
    ).toBe(400);
    expect(
      res.body,
      `The response should contain the property "message", but it does not, change the response in the function that handles the POST /api/v1/register route to return the error message`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The property unique should be true in the schema for the User model, change the schema for the User model to have the property unique on the email and username set to true`,
      options
    ).toContain("duplicate key error collection");
  });
});

afterAll(async () => {
  const collections = await mongoose.connection.db.collections();
  for (let collection of collections) {
    await collection.drop();
  }
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

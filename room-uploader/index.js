const express = require('express');
const multer = require('multer');
const cors = require('cors');
const nodemailer = require('nodemailer');
const { v4: uuidv4 } = require('uuid');
const path = require('path');
require('dotenv').config(); // This correctly loads your .env file

console.log("ENV Loaded:", {
  EMAIL_USER: process.env.EMAIL_USER,
  EMAIL_PASS: process.env.EMAIL_PASS ? '✅ Password Loaded' : '❌ Password Missing'
});

const app = express();
const port = 3000;

app.use(cors()); // You have app.use(cors()) twice, one is enough
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const storage = multer.memoryStorage();
const upload = multer({ storage: storage });

app.post('/upload', upload.array('photos', 5), async (req, res) => {
  try {
    const { address, suburb, city, rent, bond, contactName, contactNumber, description } = req.body;

    const files = req.files;
    // It's generally better to generate file names when storing them,
    // but for email attachments, original name might be fine or generated.
    // Ensure uniqueness if storing on disk.
    const fileNames = files.map((file, i) => `${uuidv4()}_${file.originalname}`);

    let html = `
      <h3>🏡 New Room Listing Uploaded</h3>
      <p><strong>Address:</strong> ${address}</p>
      <p><strong>Suburb:</strong> ${suburb}</p>
      <p><strong>City:</strong> ${city}</p>
      <p><strong>Rent:</strong> $${rent}</p>
      <p><strong>Bond:</strong> ${bond} weeks</p>
      <p><strong>Contact Name:</strong> ${contactName}</p>
      <p><strong>Contact Number:</strong> ${contactNumber}</p>
      <p><strong>Description:</strong> ${description}</p>
    `;

    // --- FIX STARTS HERE ---
    const transporter = nodemailer.createTransport({
      service: 'Outlook', // This tells Nodemailer to use Outlook's predefined settings
      auth: {
        user: process.env.EMAIL_USER, // Corrected from MY_EMAIL
        pass: process.env.EMAIL_PASS  // Corrected from MY_PASSWORD
      }
    });

    const mailOptions = {
      from: process.env.EMAIL_USER, // Corrected from MY_EMAIL
      to: process.env.EMAIL_USER,   // Corrected from MY_EMAIL (or change to a different admin email if desired)
      subject: '🏠 New Room Listing - Live in Oz',
      html: html,
      attachments: files.map((file, i) => ({
        filename: fileNames[i], // Use the generated unique name for attachment display
        content: file.buffer,
        contentType: file.mimetype // It's good practice to include content type
      }))
    };
    // --- FIX ENDS HERE ---

    await transporter.sendMail(mailOptions);

    res.status(200).send('✅ Listing uploaded & email sent for approval!');
  } catch (error) {
    console.error('❌ Error uploading listing:', error);
    res.status(500).send('Error uploading listing');
  }
});

app.listen(port, () => {
  console.log(`✅ Server running at http://localhost:${port}`);
});
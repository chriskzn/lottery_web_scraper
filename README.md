# lottery_web_scraper
Quick little PHP script to retrieve lottery results from a website - 16 January 2025

# Setup
1. I used a simple localhost MySQL database, so the username and password are left as is
2. Create your database, mine is called 'lottery_test', ensure you enter your database connection username and password.
3. Create the table using this, either through PHP Admin or any alternative method you aware of:<br>
   CREATE TABLE lottery_draws (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- Unique identifier for each record<br>
    draw_date DATE NOT NULL,             -- Date of the draw<br>
    draw_time TIME NOT NULL,             -- Time of the draw<br>
    result_number VARCHAR(20) NOT NULL,  -- Result number (e.g., #19986)<br>
    numbers VARCHAR(255) NOT NULL,       -- Winning numbers as a comma-separated string<br>
    lotto_type VARCHAR(10) NOT NULL,     -- Type of lotto (e.g., 7/49)<br>
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Timestamp for record creation<br>
);
4. Copy this file into the root of your public / (or in the example I use XAMPP and so xampp\htdocs).
5. Run the script, you should see results for Goslotto 7/49 as this is the lotto result I have asked this script to scrape from the site: https://gosloto.app/

# Enjoy, hope you able to learn and read through the code? Let me know if you require any further information?

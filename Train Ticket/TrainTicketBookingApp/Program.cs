using System;
using System.Collections;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Web.Script.Serialization;
using System.Windows.Forms;

namespace TrainTicketBookingApp
{
    internal static class Program
    {
        [STAThread]
        private static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new LoginForm());
        }
    }

    public class LoginForm : Form
    {
        private string apiUrl = "http://localhost:8000/api.php";
        private JavaScriptSerializer json = new JavaScriptSerializer();

        private TextBox txtName = new TextBox();
        private TextBox txtEmail = new TextBox();
        private TextBox txtPassword = new TextBox();
        private TextBox txtContact = new TextBox();
        private Label lblStatus = new Label();

        public LoginForm()
        {
            Text = "Passenger Access";
            StartPosition = FormStartPosition.CenterScreen;
            Size = new Size(430, 560);
            FormBorderStyle = FormBorderStyle.FixedSingle;
            MaximizeBox = false;
            BackColor = Color.FromArgb(236, 253, 245);
            Font = new Font("Segoe UI", 9);

            Panel top = new Panel();
            top.BackColor = Color.FromArgb(15, 23, 42);
            top.Location = new Point(0, 0);
            top.Size = new Size(430, 150);
            Controls.Add(top);

            Label badge = new Label();
            badge.Text = "RAIL";
            badge.BackColor = Color.FromArgb(250, 204, 21);
            badge.ForeColor = Color.FromArgb(15, 23, 42);
            badge.TextAlign = ContentAlignment.MiddleCenter;
            badge.Font = new Font("Segoe UI", 15, FontStyle.Bold);
            badge.Location = new Point(32, 32);
            badge.Size = new Size(76, 52);
            top.Controls.Add(badge);

            Label title = new Label();
            title.Text = "Passenger Portal";
            title.ForeColor = Color.White;
            title.Font = new Font("Segoe UI", 22, FontStyle.Bold);
            title.Location = new Point(32, 92);
            title.Size = new Size(340, 40);
            top.Controls.Add(title);

            Panel card = new Panel();
            card.BackColor = Color.White;
            card.Location = new Point(32, 175);
            card.Size = new Size(350, 305);
            Controls.Add(card);

            AddLabel(card, "Full Name", 24, 20);
            txtName.Location = new Point(24, 42);
            txtName.Size = new Size(302, 28);
            txtName.Text = "Juan Passenger";
            card.Controls.Add(txtName);

            AddLabel(card, "Email", 24, 78);
            txtEmail.Location = new Point(24, 100);
            txtEmail.Size = new Size(302, 28);
            txtEmail.Text = "passenger@railway.test";
            card.Controls.Add(txtEmail);

            AddLabel(card, "Password", 24, 136);
            txtPassword.Location = new Point(24, 158);
            txtPassword.Size = new Size(302, 28);
            txtPassword.PasswordChar = '*';
            txtPassword.Text = "passenger123";
            card.Controls.Add(txtPassword);

            AddLabel(card, "Contact", 24, 194);
            txtContact.Location = new Point(24, 216);
            txtContact.Size = new Size(302, 28);
            txtContact.Text = "09123456789";
            card.Controls.Add(txtContact);

            Button login = new Button();
            login.Text = "LOGIN";
            login.Location = new Point(24, 260);
            login.Size = new Size(145, 36);
            StyleButton(login, Color.FromArgb(4, 120, 87));
            login.Click += delegate { LoginPassenger(); };
            card.Controls.Add(login);

            Button register = new Button();
            register.Text = "REGISTER";
            register.Location = new Point(181, 260);
            register.Size = new Size(145, 36);
            StyleButton(register, Color.FromArgb(37, 99, 235));
            register.Click += delegate { RegisterPassengerTodo(); };
            card.Controls.Add(register);

            lblStatus.ForeColor = Color.FromArgb(4, 120, 87);
            lblStatus.TextAlign = ContentAlignment.MiddleCenter;
            lblStatus.Location = new Point(32, 495);
            lblStatus.Size = new Size(350, 28);
            lblStatus.Font = new Font("Segoe UI", 8, FontStyle.Bold);
            Controls.Add(lblStatus);
        }

        private void AddLabel(Control parent, string text, int x, int y)
        {
            Label label = new Label();
            label.Text = text;
            label.Location = new Point(x, y);
            label.Size = new Size(140, 20);
            label.Font = new Font("Segoe UI", 8, FontStyle.Bold);
            label.ForeColor = Color.FromArgb(51, 65, 85);
            parent.Controls.Add(label);
        }

        private void StyleButton(Button b, Color color)
        {
            b.BackColor = color;
            b.ForeColor = Color.White;
            b.FlatStyle = FlatStyle.Flat;
            b.FlatAppearance.BorderSize = 0;
            b.Font = new Font("Segoe UI", 8, FontStyle.Bold);
        }

        private string PostApi(Dictionary<string, string> data)
        {
            using (WebClient client = new WebClient())
            {
                client.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";

                List<string> parts = new List<string>();

                foreach (KeyValuePair<string, string> item in data)
                {
                    parts.Add(Uri.EscapeDataString(item.Key) + "=" + Uri.EscapeDataString(item.Value));
                }

                try
                {
                    return client.UploadString(apiUrl, "POST", string.Join("&", parts.ToArray()));
                }
                catch (WebException ex)
                {
                    string msg = ReadError(ex);
                    throw new Exception("API Request Failed: " + msg);
                }
            }
        }

        private string ReadError(WebException ex)
        {
            try
            {
                if (ex.Response != null)
                {
                    if (ex.Response == null)
                        return ex.Message;

                    using (Stream stream = ex.Response.GetResponseStream())
                    {
                        if (stream == null)
                            return ex.Message;
                        using (StreamReader reader = new StreamReader(stream))
                    {
                        string body = reader.ReadToEnd();
                        Dictionary<string, object> data = json.Deserialize<Dictionary<string, object>>(body);

                        if (data != null && data.ContainsKey("message"))
                        {
                            return Convert.ToString(data["message"]);
                        }

                        return body;
                    }
                }
            }
            catch { }

            return ex.Message;
        }

        private void LoginPassenger()
        {
            try
            {
                string response = PostApi(new Dictionary<string, string>
                {
                    {"action","login"},
                    {"email",txtEmail.Text.Trim()},
                    {"password",txtPassword.Text.Trim()},
                    {"role","passenger"}
                });

                var result = json.Deserialize<Dictionary<string, object>>(response);

                if (result == null || !result.ContainsKey("user") || result["user"] == null)
                    throw new Exception("Invalid login response from server.");

                var user = result["user"] as Dictionary<string, object>;

                if (user == null)
                    throw new Exception("User data is corrupted.");

                int id = Convert.ToInt32(user["id"]);
                string name = Convert.ToString(user["name"]);
                string email = Convert.ToString(user["email"]);

                PassengerForm main = new PassengerForm(id, name, email);
                main.FormClosed += delegate
                {
                    if (Convert.ToString(main.Tag) == "logout")
                    {
                        txtPassword.Clear();
                        lblStatus.Text = "Logged out.";
                        Show();
                    }
                    else
                    {
                        Close();
                    }
                };

                Hide();
                main.Show();
            }
            catch (Exception ex)
            {
                MessageBox.Show("Login failed.\n\n" + ex.Message);
            }
        }

        private void RegisterPassengerTodo()
        {
            MessageBox.Show("Passenger registration is not connected yet. Follow the commit guide.");
        }
    }

    public class PassengerForm : Form
    {
        private string apiUrl = "http://localhost:8000/api.php";
        private JavaScriptSerializer json = new JavaScriptSerializer();

        private int passengerId;
        private string passengerName;
        private string passengerEmail;

        private Panel content = new Panel();
        private DataGridView gridTrains = new DataGridView();
        private DataGridView gridTickets = new DataGridView();
        private ComboBox cboTrain = new ComboBox();
        private DateTimePicker dtTravel = new DateTimePicker();
        private NumericUpDown numSeats = new NumericUpDown();
        private Label lblStatus = new Label();

        public PassengerForm(int id, string name, string email)
        {
            passengerId = id;
            passengerName = name;
            passengerEmail = email;

            Text = "Passenger Ticket Booking";
            StartPosition = FormStartPosition.CenterScreen;
            Size = new Size(1130, 720);
            MinimumSize = new Size(1000, 640);
            BackColor = Color.FromArgb(236, 253, 245);
            Font = new Font("Segoe UI", 9);
            AutoScroll = true;

            BuildUi();

            Load += delegate { RefreshAll(); };
            Resize += delegate { CenterContent(); };
        }

        private void BuildUi()
        {
            content.Size = new Size(1060, 720);
            content.Location = new Point(18, 15);
            content.BackColor = Color.Transparent;
            Controls.Add(content);

            Panel side = new Panel();
            side.Location = new Point(0, 0);
            side.Size = new Size(230, 640);
            side.BackColor = Color.FromArgb(15, 23, 42);
            content.Controls.Add(side);

            Label logo = new Label();
            logo.Text = "PASS";
            logo.BackColor = Color.FromArgb(250, 204, 21);
            logo.ForeColor = Color.FromArgb(15, 23, 42);
            logo.TextAlign = ContentAlignment.MiddleCenter;
            logo.Font = new Font("Segoe UI", 17, FontStyle.Bold);
            logo.Location = new Point(24, 24);
            logo.Size = new Size(88, 58);
            side.Controls.Add(logo);

            Label title = new Label();
            title.Text = "Passenger\nTicket Booking";
            title.ForeColor = Color.White;
            title.Font = new Font("Segoe UI", 16, FontStyle.Bold);
            title.Location = new Point(24, 102);
            title.Size = new Size(185, 70);
            side.Controls.Add(title);

            Label info = new Label();
            info.Text = passengerName + "\n" + passengerEmail;
            info.ForeColor = Color.FromArgb(209, 250, 229);
            info.Font = new Font("Segoe UI", 8, FontStyle.Bold);
            info.Location = new Point(24, 190);
            info.Size = new Size(185, 64);
            side.Controls.Add(info);

            Button logout = new Button();
            logout.Text = "LOGOUT";
            logout.Location = new Point(24, 560);
            logout.Size = new Size(182, 38);
            StyleButton(logout, Color.FromArgb(153, 27, 27));
            logout.Click += delegate
            {
                if (MessageBox.Show("Logout and return to login?", "Logout", MessageBoxButtons.YesNo) == DialogResult.Yes)
                {
                    Tag = "logout";
                    Close();
                }
            };
            side.Controls.Add(logout);

            Panel trains = PanelCard(255, 0, 790, 245, "Available Trains");
            content.Controls.Add(trains);

            gridTrains.Location = new Point(18, 58);
            gridTrains.Size = new Size(754, 165);
            ConfigureGrid(gridTrains);
            trains.Controls.Add(gridTrains);

            Panel booking = PanelCard(255, 265, 790, 150, "Book Ticket");
            content.Controls.Add(booking);

            Label l1 = LabelText("Train", 18, 60);
            booking.Controls.Add(l1);
            cboTrain.Location = new Point(75, 57);
            cboTrain.Size = new Size(340, 27);
            cboTrain.DropDownStyle = ComboBoxStyle.DropDownList;
            booking.Controls.Add(cboTrain);

            Label l2 = LabelText("Date", 430, 60);
            booking.Controls.Add(l2);
            dtTravel.Location = new Point(475, 57);
            dtTravel.Size = new Size(130, 27);
            dtTravel.Format = DateTimePickerFormat.Short;
            booking.Controls.Add(dtTravel);

            Label l3 = LabelText("Seats", 620, 60);
            booking.Controls.Add(l3);
            numSeats.Location = new Point(670, 57);
            numSeats.Size = new Size(60, 27);
            numSeats.Minimum = 1;
            numSeats.Maximum = 10;
            numSeats.Value = 1;
            booking.Controls.Add(numSeats);

            Button book = new Button();
            book.Text = "SUBMIT BOOKING";
            book.Location = new Point(475, 100);
            book.Size = new Size(160, 36);
            StyleButton(book, Color.FromArgb(4, 120, 87));
            book.Click += delegate { BookTicketTodo(); };
            booking.Controls.Add(book);

            Button refresh = new Button();
            refresh.Text = "REFRESH";
            refresh.Location = new Point(650, 100);
            refresh.Size = new Size(80, 36);
            StyleButton(refresh, Color.FromArgb(37, 99, 235));
            refresh.Click += delegate { RefreshAll(); };
            booking.Controls.Add(refresh);

            Panel tickets = PanelCard(255, 435, 790, 205, "My Tickets");
            content.Controls.Add(tickets);

            gridTickets.Location = new Point(18, 58);
            gridTickets.Size = new Size(754, 125);
            ConfigureGrid(gridTickets);
            tickets.Controls.Add(gridTickets);

            lblStatus.Location = new Point(255, 660);
            lblStatus.Size = new Size(790, 30);
            lblStatus.BackColor = Color.White;
            lblStatus.ForeColor = Color.FromArgb(15, 23, 42);
            lblStatus.Font = new Font("Segoe UI", 8, FontStyle.Bold);
            lblStatus.TextAlign = ContentAlignment.MiddleLeft;
            lblStatus.Padding = new Padding(10, 0, 0, 0);
            content.Controls.Add(lblStatus);

            CenterContent();
        }

        private void CenterContent()
        {
            int x = 18;
            if (ClientSize.Width > content.Width + 30)
            {
                x = (ClientSize.Width - content.Width) / 2;
            }
            content.Location = new Point(x, 15);
        }

        private Panel PanelCard(int x, int y, int w, int h, string title)
        {
            Panel p = new Panel();
            p.Location = new Point(x, y);
            p.Size = new Size(w, h);
            p.BackColor = Color.White;

            Label accent = new Label();
            accent.BackColor = Color.FromArgb(4, 120, 87);
            accent.Location = new Point(0, 0);
            accent.Size = new Size(7, h);
            p.Controls.Add(accent);

            Label t = new Label();
            t.Text = title;
            t.ForeColor = Color.FromArgb(4, 120, 87);
            t.Font = new Font("Segoe UI", 13, FontStyle.Bold);
            t.Location = new Point(18, 18);
            t.Size = new Size(w - 40, 30);
            p.Controls.Add(t);

            return p;
        }

        private Label LabelText(string text, int x, int y)
        {
            Label l = new Label();
            l.Text = text;
            l.Location = new Point(x, y);
            l.Size = new Size(55, 25);
            l.Font = new Font("Segoe UI", 8, FontStyle.Bold);
            l.ForeColor = Color.FromArgb(51, 65, 85);
            return l;
        }

        private void StyleButton(Button b, Color color)
        {
            b.BackColor = color;
            b.ForeColor = Color.White;
            b.FlatStyle = FlatStyle.Flat;
            b.FlatAppearance.BorderSize = 0;
            b.Font = new Font("Segoe UI", 8, FontStyle.Bold);
        }

        private void ConfigureGrid(DataGridView g)
        {
            g.AllowUserToAddRows = false;
            g.AllowUserToDeleteRows = false;
            g.ReadOnly = true;
            g.RowHeadersVisible = false;
            g.SelectionMode = DataGridViewSelectionMode.FullRowSelect;
            g.MultiSelect = false;
            g.AutoSizeColumnsMode = DataGridViewAutoSizeColumnsMode.Fill;
            g.BackgroundColor = Color.White;
            g.BorderStyle = BorderStyle.FixedSingle;
            g.EnableHeadersVisualStyles = false;
            g.ColumnHeadersDefaultCellStyle.BackColor = Color.FromArgb(15, 23, 42);
            g.ColumnHeadersDefaultCellStyle.ForeColor = Color.FromArgb(209, 250, 229);
            g.ColumnHeadersDefaultCellStyle.Font = new Font("Segoe UI", 8, FontStyle.Bold);
            g.DefaultCellStyle.SelectionBackColor = Color.FromArgb(4, 120, 87);
            g.DefaultCellStyle.SelectionForeColor = Color.White;
            g.DefaultCellStyle.Font = new Font("Segoe UI", 8);
            g.RowTemplate.Height = 25;
        }

        private Dictionary<string, object> GetApi(string query)
        {
            using (WebClient client = new WebClient())
            {
                string response = client.DownloadString(apiUrl + "?" + query);
                return json.Deserialize<Dictionary<string, object>>(response);
            }
        }

        private void RefreshAll()
        {
            try
            {
                LoadTrains();
                LoadTicketsTodo();
                lblStatus.Text = "Connected.";
            }
            catch (Exception ex)
            {
                MessageBox.Show("Connection failed.\n\nRun PHP first inside php-api:\nphp -S localhost:8000\n\n" + ex.Message);
            }
        }

        private void LoadTrains()
        {
            if (!result.ContainsKey("user") || result["user"] == null)
                throw new Exception("Invalid login response from server.");

            Dictionary<string, object> user = result["user"] as Dictionary<string, object>;
            if (user == null)
                throw new Exception("User data is corrupted.");
            ArrayList rows = null;

            if (result.ContainsKey("trains"))
                rows = result["trains"] as ArrayList;
            gridTrains.Columns.Clear();
            gridTrains.Rows.Clear();

            gridTrains.Columns.Add("id", "ID");
            gridTrains.Columns.Add("code", "Code");
            gridTrains.Columns.Add("name", "Train");
            gridTrains.Columns.Add("route", "Route");
            gridTrains.Columns.Add("time", "Time");
            gridTrains.Columns.Add("fare", "Fare");
            gridTrains.Columns.Add("seats", "Seats");

            cboTrain.Items.Clear();

            if (rows != null)
            {
                foreach (object obj in rows)
                {
                    Dictionary<string, object> t = obj as Dictionary<string, object>;
                    if (t == null) continue;

                    string route = t["origin_station"] + " to " + t["destination_station"];
                    string time = t["departure_time"] + " - " + t["arrival_time"];

                    gridTrains.Rows.Add(t["id"], t["train_code"], t["train_name"], route, time, t["fare"], t["available_seats"]);
                   
                }
            }

            if (cboTrain.Items.Count > 0) cboTrain.SelectedIndex = 0;
        }

        private string GetSelectedTrainId()
        {
            if (cboTrain.SelectedItem is ComboItem item)
                return item.Value;

            return null;
        }

        private void LoadTicketsTodo()
        {
            gridTickets.Columns.Clear();
            gridTickets.Rows.Clear();

            gridTickets.Columns.Add("message", "Ticket status");
            gridTickets.Rows.Add("Ticket status loading will be implemented in a later commit.");
        }

        private void BookTicketTodo()
        {
            MessageBox.Show("Ticket booking is not connected yet. Follow the commit guide.");
        }

        private class ComboItem
        {
            public string Text;
            public string Value;

            public ComboItem(string text, string value)
            {
                Text = text;
                Value = value;
            }

            public override string ToString()
            {
                return Text;
            }
        }
    }
}

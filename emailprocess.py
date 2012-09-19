#!/usr/bin/python2

import imaplib,re,email,MySQLdb,smtplib
from email.mime.text import MIMEText

mysql = MySQLdb.connect('localhost','yrswebuser','sndTDaEqDerGr643','yrstest')
c=mysql.cursor()

def send_notification_email(address,threadkey):
	message = """Hello,

You've received a reply to a message you're watching on Lobby-O-Matic.
To view it, follow the link below

{0}

Yours,

The Lobby-O-Matic Team""".format('http://www.toastwaffle.com/lobby-o-matic/viewthread.php?threadkey='+threadkey)
	msg = MIMEText(message)

	# me == the sender's email address
	# you == the recipient's email address
	msg['Subject'] = 'Response received on Lobby-O-Matic'
	msg['From'] = 'lobbyomatic+noreply@toastwaffle.com'
	msg['To'] = address

	# Send the message via our own SMTP server, but don't include the
	# envelope header.
	s = smtplib.SMTP_SSL('smtp.googlemail.com',465)
	s.login('lobbyomatic@toastwaffle.com', 'citamoybbol')
	s.sendmail('lobbyomatic+noreply@toastwaffle.com', [address], msg.as_string())
	s.quit()

def get_first_text_block(email_message_instance):
	maintype = email_message_instance.get_content_maintype()
	if maintype == 'multipart':
		for part in email_message_instance.get_payload():
			if part.get_content_maintype() == 'text':
				return part.get_payload()
	elif maintype == 'text':
		return email_message_instance.get_payload()

mail = imaplib.IMAP4_SSL('imap.gmail.com')
mail.login('lobbyomatic@toastwaffle.com', 'citamoybbol')
mail.list()
mail.select("inbox")
result, data = mail.uid('search', None, "ALL")
for uid in data[0].split():
	result, data = mail.uid('fetch', uid, '(RFC822)')
	raw_email = data[0][1]

	email_message = email.message_from_string(raw_email)

	tomatch = re.match('lobbyomatic\+(.*?)@toastwaffle\.com',email_message['To'])
	if tomatch:
		emailkey = tomatch.groups()[0]
		fromaddress =  email.utils.parseaddr(email_message['From'])[1]
		fromname =  email.utils.parseaddr(email_message['From'])[0]
		emailtext = get_first_text_block(email_message)

		replytext = ""

		for line in iter(emailtext.splitlines()):
			if line == "-- ":
				print "break1"
				break
			if line == "--":
				print "break2"
				break
			if re.match('-----Original Message-----.*?',line):
				print "break3"
				break
			if re.match('________________________________.*?',line):
				print "break4"
				break
			if re.match("On .*? wrote:",line):
				print "break5"
				break
			if re.match('From:.*?',line):
				print "break6"
				break
			if re.match('Sent from my iPhone.*?',line):
				print "break7"
				break
			if re.match('Sent from my BlackBerry.*?',line):
				print "break8"
				break
			if re.match('\>.*?',line):
				print "break9"
				break
			replytext = replytext + line + "\n"

		c.execute("""INSERT INTO Emails (threadkey,fromname,fromemail,message,type) 
			VALUES (%s,%s,%s,%s,%s)""", (emailkey,fromname,fromaddress,replytext,'received',))

		c.execute("""SELECT `Users`.`email` FROM `Users` INNER JOIN `Watchers` ON 
			`Users`.`id` = `Watchers`.`userid` INNER JOIN `Threads` ON 
			`Watchers`.`threadid` = `Threads`.`id` WHERE `Threads`.`threadkey` = %s""", (emailkey,))

		for row in c.fetchall():
			send_notification_email(row[0],emailkey)

		result = mail.uid('COPY', uid, '[Gmail]/All Mail')
		if result[0] == 'OK':
			mov, data = mail.uid('STORE', uid , '+FLAGS', '(\Deleted)')
			mail.expunge()

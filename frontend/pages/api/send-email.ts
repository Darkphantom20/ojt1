import type { NextApiRequest, NextApiResponse } from 'next';
import sendgrid from '@sendgrid/mail';

sendgrid.setApiKey(process.env.SENDGRID_API_KEY ?? '');

type Data = {
  success: boolean;
  message: string;
};

export default async function handler(
  req: NextApiRequest,
  res: NextApiResponse<Data>
) {
  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: 'Method not allowed' });
  }

  const { to, subject, html } = req.body;

  if (!to || !subject || !html) {
    return res.status(400).json({ success: false, message: 'Missing email fields' });
  }

  try {
    await sendgrid.send({
      to,
      from: process.env.SENDGRID_FROM_EMAIL ?? 'no-reply@yourdomain.com',
      subject,
      html,
    });

    return res.status(200).json({ success: true, message: 'Email sent' });
  } catch (error: any) {
    console.error('SendGrid error', error);
    return res.status(500).json({
      success: false,
      message: error?.message || 'Failed to send email',
    });
  }
}

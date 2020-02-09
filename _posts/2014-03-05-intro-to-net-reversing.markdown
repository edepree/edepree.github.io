---
layout: post
title:  "Intro to .NET Reversing"
---

When I was asked by SecDaemons to give a talk to the club about a security topic I was excited about the opportunity. The best way to learn something is by teaching it and I wanted to learn more about reversing .NET assemblies. I have created two write-ups for .NET challenges from CSAW which have helped me learn how to use some of the reversing tools. These challenges are easy in scope and a lot of fun.

#### Reversing 300 - CSAW 2012

For this challenge, running the executable starts a console application asking the user if they really run random binaries on their system. Any answer to this question will result in the program terminating. After running the binary though a decompiler the source code is generated and a snippet of that is below.

{% highlight c# %}
static Program()
{
  Program.data = new byte[] { 15, 83, 222, 204, 130, 169, 253, 55, 165, 229, 219, 240, 206, 78, 102, 131, 243, 100, 115, 102, 231, 76, 235, 175, 2, 193, 249, 172, 174, 172, 227, 120, 67, 118, 87, 221, 124, 97, 202, 124, 191, 209, 164, 8, 61, 224, 193, 83, 13, 137, 114, 140, 42, 65, 247, 237, 202, 71, 66, 38, 58, 205, 158, 199, 246, 205, 178, 248, 21, 55, 82, 239, 36, 107, 104, 230, 193, 63, 157, 178, 224, 48, 198, 4, 66, 221, 12, 211, 215, 103, 209, 14, 117, 139, 111, 162 };
  Program.marker = new byte[] { 255, 151, 169, 253, 237, 224, 158, 175, 110, 28, 142, 201, 246, 166, 29, 213 };
  Program.target = "C:\\Program Files\\";
}

private static void Main(string[] args)
{
  Console.WriteLine("Do you really just run random binaries given to you in challenges?");
  Console.ReadLine();

  Environment.Exit(0);

  MD5CryptoServiceProvider mD5CryptoServiceProvider = new MD5CryptoServiceProvider();
  AesCryptoServiceProvider aesCryptoServiceProvider = new AesCryptoServiceProvider();

  foreach (string str in Directory.EnumerateDirectories(Program.target))
  {
    if (!mD5CryptoServiceProvider.ComputeHash(Encoding.UTF8.GetBytes(str.Replace(Program.target, ""))).SequenceEqual&lt;byte&gt;(Program.marker))
    {
      continue;
    }

    byte[] numArray = mD5CryptoServiceProvider.
    ComputeHash(Encoding.UTF8.GetBytes(string.Concat("sneakyprefix", str.Replace(Program.target, ""))));
    ICryptoTransform cryptoTransform = aesCryptoServiceProvider.CreateDecryptor(numArray, new byte[] { 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15 });
    byte[] numArray1 = cryptoTransform.TransformFinalBlock(Program.data, 0, (int)Program.data.Length);
    Console.Write(Encoding.UTF7.GetString(numArray1));
  }

  Console.ReadLine();
}
{% endhighlight %}

The third line in the Main function shows the first issue. There is a exit command prematurely terminating the program. Since this is a small program it can be fixed in-place easy enough. Using the [Reflexil][1] extension for the decompiler, a patched executable can be created which has the exit command and its argument removed.

![Instructions to Remove]({{ site.github.url }}/assets/images/002/01.png){:class="img-responsive center-margin"}

Running the patched program at this point causes it to hang showing there must be more to this assembly then just a premature termination. In the code, the program is looking for a specific folder on disk. The first part of the path is easy since **C:\Program Files\\** is in plain text. This folder already exists in Windows so there something else that is missing. The code is using the **Program.marker** variable to search for a subdirectory of Program Files. It's also using a MD5 hash library to query for the folder instead of a plain text folder name. Converting the byte array into it's hex equivalent produces FF97A9FDEDE09EAF6E1C8EC9F6A61DD5 and decoded it becomes 'Intel'. The final step is to create this folder and run the patched program again which generates the key.

![Key Generated from Assembly]({{ site.github.url }}/assets/images/002/02.png){:class="img-responsive center-margin"}

The binary for this can be found [here][2].

#### Reversing 150 - CSAW 2013

This reversing challenge is another fun puzzle to solve. Running the executable launches a WinForm application with a single text box and button. Entering an input into the box and submitting it displays assorted messages that does not help solve the problem.

![Application with 'password' entered into the input box]({{ site.github.url }}/assets/images/002/03.png){:class="img-responsive center-margin"}

Viewing the decompiled source of this application it is obvious that the code has been obfuscated. Running the application through [de4dot][3] to strip this does not help very much. The first area to look at in solving this is what happens when the button is clicked. The snippet of this method shows that there is an If...Else block which will either show the key to the user or display a failure message.

{% highlight c# %}
private void button1_Click(object sender, EventArgs e)
{
  string str = null;
  Assembly executingAssembly = Assembly.GetExecutingAssembly();
  ResourceManager resourceManager = new ResourceManager(string.Concat(executingAssembly.GetName().Name, ".Resources"), executingAssembly);
  DateTime now = DateTime.Now;
  string text = this.textBox1.Text;
  string str1 = string.Format("{0}", now.Hour + 1);
  this.method_7("NeEd_MoRe_Bawlz", Convert.ToInt32(str1), ref str);
  if (string.Compare(text.ToUpper(), str) != 0)
  {
    this.pictureBox1.Image = (Bitmap)resourceManager.GetObject("Almost There");
    this.method_5();
  }
  else
  {
    this.messageText.Text = "";
    Form1 form1 = this;
    form1.method_3(form1.method_2(107));
    this.method_4();
    this.messageText.Text = string.Format(this.messageText.Text, this.method_8(resourceManager));
    this.pictureBox1.Image = (Bitmap)resourceManager.GetObject("Sorry You Suck");
  }
}
{% endhighlight %}

This code shows that based on the time on day and other variables a string is generated. That string is compared to what the user enters and if they match the key is display. The approach to solving the puzzle is to understand how everything is generated and enter the correct information in the application. There is also another easier way to solve the puzzle with Visual Studio. Once the application is compiled and running the debugging functionality of Visual Studio can be leveraged to examine memory at runtime.

![Breakpoint Showing Variables in Memory]({{ site.github.url }}/assets/images/002/04.png){:class="img-responsive center-margin"}

Stopping at this breakpoint the input of the user is displayed as 'password'. Also, the value of the correct input is displayed in the **str** variable. By copying this value and pasting it into the text box of the application the key is then displayed to the user.

![The Answer Displayed to the User]({{ site.github.url }}/assets/images/002/05.png){:class="img-responsive center-margin"}

The binary for this can be found [here][4].

#### Update - 03/19/14

The slides to the presentation for the talk can be found [here][5].

[1]: http://reflexil.net/
[2]: /assets/files/002/CSAWQualification.exe
[3]: https://github.com/0xd4d/de4dot
[4]: /assets/files/002/bikinibonanza.exe
[5]: /assets/files/002/presentation.pdf

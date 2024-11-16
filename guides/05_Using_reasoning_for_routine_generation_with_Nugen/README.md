## **Nugen Intelligence**
<img src="https://nugen.in/logo.png" alt="Nugen Logo" width="200"/>

# **Using Reasoning for Routine Generation with Nugen API**
 
This code demonstrates how to generate programmatically executable routines from help center articles using the Nugen API. The goal is to transform external-facing help center content into internal-facing routines that can be used by a large language model (LLM) to handle customer service tasks effectively.

## **Importing Necessary Libraries**


```python
import requests
from IPython.display import display, HTML
import pandas as pd
from concurrent.futures import ThreadPoolExecutor
import csv

url_api_server = "https://api.nugen.in"
api_key =  <GET YOUR NUGEN API KEYS> # Replace with your actual Nugen API key
MODEL = "nugen-flash-instruct"
```

1. requests: A library to make HTTP
2. requests to the Nugen API.
3. pandas: A library to handle data manipulation and analysis.
4. ThreadPoolExecutor: A tool to run multiple tasks concurrently, speeding up processing.
5. csv: A module to read and write CSV files.

## **Setting Up the Nugen API**


```python
class NugenAPIClient:
    def __init__(self, base_url, api_key):
        self.base_url = base_url
        self.api_key = api_key

    def chat_completions_create(self, model, messages, max_tokens=400, temperature=1):
        url = f"{self.base_url}/inference/completions"
        headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }
        prompt = "\n".join([message["content"] for message in messages])

        payload = {
            "model": model,
            "prompt": prompt,
            "max_tokens": max_tokens,
            "temperature": temperature
        }

        response = requests.post(url, json=payload, headers=headers)

        if response.status_code == 200:
            return response.json()
        else:
            raise Exception(f"Error {response.status_code}: {response.text}")

# Instantiate Nugen client
client = NugenAPIClient(base_url=url_api_server, api_key=api_key)

```

**url:** The endpoint for the Nugen API where we send our requests.

**headers:** Includes the authorization token for API access and specifies that we're sending JSON data.

## **Defining the Routine Generation Function**


```python
CONVERSION_PROMPT = """
You are a helpful assistant tasked with taking an external facing help center article and converting it into an internal-facing programmatically executable routine optimized for an LLM.
The LLM using this routine will be tasked with reading the policy, answering incoming questions from customers, and helping drive the case toward resolution.

Please follow these instructions:
1. **Review the customer service policy carefully** to ensure every step is accounted for. It is crucial not to skip any steps or policies.
2. **Organize the instructions into a logical, step-by-step order**, using the specified format.
3. **Use the following format**:
   - **Main actions are numbered** (e.g., 1, 2, 3).
   - **Sub-actions are lettered** under their relevant main actions (e.g., 1a, 1b).
      **Sub-actions should start on new lines**
   - **Specify conditions using clear 'if...then...else' statements** (e.g., 'If the product was purchased within 30 days, then...').
   - **For instructions that require more information from the customer**, provide polite and professional prompts to ask for additional information.
   - **For actions that require data from external systems**, write a step to call a function using backticks for the function name (e.g., `call the check_delivery_date function`).
      - **If a step requires the customer service agent to take an action** (e.g., process a refund), generate a function call for this action (e.g., `call the process_refund function`).
      - **Define any new functions** by providing a brief description of their purpose and required parameters.
   - **If there is an action an assistant can perform on behalf of the user**, include a function call for this action (e.g., `call the change_email_address function`), and ensure the function is defined with its purpose and required parameters.
      - This action may not be explicitly defined in the help center article, but can be done to help the user resolve their inquiry faster.
   - **The step prior to case resolution should always be to ask if there is anything more you can assist with**.
   - **End with a final action for case resolution**: calling the `case_resolution` function should always be the final step.
4. **Ensure compliance** by making sure all steps adhere to company policies, privacy regulations, and legal requirements.
5. **Handle exceptions or escalations** by specifying steps for scenarios that fall outside the standard policy.

**Important**: If at any point you are uncertain, respond with "I don't know."

Please convert the customer service policy into the formatted routine, ensuring it is easy to follow and execute programmatically.
"""

# Read articles from CSV
articles = []
with open('helpcenter_articles.csv', mode='r', encoding='utf-8') as file:
    reader = csv.DictReader(file)
    for row in reader:
        articles.append({
            "policy": row["policy"],
            "content": row["content"]
        })

# Function to generate routine using Nugen API
# Debugging step - Print full response to check its structure
def generate_routine(policy):
    try:
        messages = [{
            "role": "user",
            "content": f"{CONVERSION_PROMPT}\n\nPOLICY:\n{policy}"
        }]

        response = client.chat_completions_create(
            model=MODEL,
            messages=messages
        )

        # Print the full response to debug
        print("Full API Response:", response)

        # Modify this according to the actual response structure
        return response['choices'][0].get('text', None)  # Adjust based on actual API response
    except Exception as e:
        print(f"An error occurred: {e}")
        return None


```

**generate_routine(policy):** This function sends a request to the Nugen API to convert a help center article (policy) into an internal-facing routine.
  1. payload: Contains the request parameters, including the prompt which outlines how to format the routine.
  2. response: Receives the response from the API and extracts the generated routine from it.
  3. Exception handling: Catches any errors that occur during the request and prints an error message.

## **Processing Articles Concurrently**



```python
# Function to process each article and generate routine
def process_article(article):
    routine = generate_routine(article['content'])
    return {"policy": article['policy'], "content": article['content'], "routine": routine}

# Execute routine generation concurrently
with ThreadPoolExecutor() as executor:
    results = list(executor.map(process_article, articles))

```

    Full API Response: {'id': 'nugen-1726663967.901725', 'created': 1726663967.901725, 'model': 'nugen-flash-instruct', 'choices': [{'text': ' \nDo you provide a data processing agreement?\nYes, we can provide a DPA, but it is typically more expensive and requires more back-and-forth with our team.\n\n \nCan I obtain a copy of the executed BAA?\nOnce you sign a BAA with OpenAI, you will receive a copy of the executed agreement.\n\n\nPlease note that we are not able to provide a HIPAA-compliant API platform to companies that don’t have an enterprise agreement with us.\n\n\nHere is the formatted routine for an internal-facing LLM:\n\n**Main Actions:**\n\n1. **Initial Request**\n   a. Receive the request for a Business Associate Agreement (BAA) from the customer.\n   b. If the request is for a BAA for ChatGPT Enterprise, then redirect the customer to contact sales.\n   c. Otherwise, proceed to Step 1d.\n   d. Respond to the customer with the following message: "Thank you for your interest in obtaining a Business Associate Agreement (BAA) with OpenAI. We will review your request and respond within 1-2 business days. Please provide more information about your company and use case so we can better assist you."\n   e. `call the get_customer_info function` to retrieve the customer\'s company and use case details.\n\n2. **Review and Evaluation**\n   a. Receive the customer\'s company and use case details.\n   b. Review the customer\'s request on a case-by-case basis.\n   c. If additional information is required, then send a request to the customer for more details.\n   d. `call the evaluate_request function` to determine if the customer\'s use case is eligible for a BAA.\n   e. If the request is approved, then proceed to Step 3.\n   f. If the request is not approved, then proceed to Step 4.\n\n3. **BAA Approval and Execution**\n   a. If the customer\'s request is approved, then send a confirmation message to the', 'index': 0, 'finish_reason': 'length'}], 'usage': {'prompt_tokens': 912, 'completion_tokens': 400, 'total_tokens': 1312}}
    Full API Response: {'id': 'nugen-1726663988.49748', 'created': 1726663988.49748, 'model': 'nugen-flash-instruct', 'choices': [{'text': '\n    5. Organization ID\n    6. Invoice number\n\nTo find the organization ID and invoice number: \n    1. Go to your Billing preferences\n    2. Select the billing address associated with the account you are trying to apply a VAT exemption to\n    3. Look for the organization ID and invoice number in the "Details" section\n\n \n\n**How to submit your VAT exemption request** \n    1. Open the chat widget in the bottom-right corner\n    2. Select the \'VAT exemption request\' topic\n    3. Fill in the form with the required information and attach a copy of your certificate/form\n    4. Click \'Submit request\'\n\nYou can also email your request to us. Please include the same information as the form, and attach a copy of your certificate/form.\n\n \n\n**What to expect after submitting your request** \n    1. We will review your request and apply the VAT exemption to your account if everything is in order\n    2. We will send you a confirmation email once the exemption has been applied\n    3. If we need more information, we will contact you via email or phone\n\n \n\n**Additional tips** \n    If you are unsure about the process, please contact us. We are here to help.\n    You can also find more information on our website, including our VAT exemption policy and FAQs.\n\nHere is the formatted routine:\n\n\n1. Review the customer\'s VAT exemption request form to ensure all required information is provided.\n    a. Check for the customer\'s name and address.\n    b. Check for the tax exemption number.\n    c. Check for signatures and dates.\n    d. If any required information is missing, then ask the customer to provide the missing details.\n    e. If the customer has submitted a VAT exemption request via email, then request the customer to submit the request through the chat widget.\n\n2. Verify the customer\'s account information to ensure', 'index': 0, 'finish_reason': 'length'}], 'usage': {'prompt_tokens': 833, 'completion_tokens': 400, 'total_tokens': 1233}}
    Full API Response: {'id': 'nugen-1726664006.031152', 'created': 1726664006.031152, 'model': 'nugen-flash-instruct', 'choices': [{'text': "Please note that deleting a payment method will not automatically cancel your subscription. If you'd like to cancel your service, please follow the instructions above for ChatGPT Plus or API.\n\n## External Services\nIf you have a payment method stored with one of our external services (e.g. a third-party app), you'll need to contact the service provider directly to cancel or delete the payment method.\n\nPlease let us know if you have any other questions or if there's anything else we can help with.\n\nI'll be happy to assist you further.", 'index': 0, 'finish_reason': 'stop'}], 'usage': {'prompt_tokens': 817, 'completion_tokens': 110, 'total_tokens': 927}}
    Full API Response: {'id': 'nugen-1726664015.181412', 'created': 1726664015.181412, 'model': 'nugen-flash-instruct', 'choices': [{'text': 'Please note that there are risks of data loss and service disruption associated with exceeding your prepaid billing quota.\n\nAPI Usage \nAPI usage will be blocked if you do not have enough credits to cover the request. If you have remaining credits, the system will apply the usage to the credits. When the credits are depleted, the system will stop the API usage until the credits are replenished.', 'index': 0, 'finish_reason': 'stop'}], 'usage': {'prompt_tokens': 999, 'completion_tokens': 77, 'total_tokens': 1076}}


1. process_article(article): Calls the generate_routine function for each article and returns a dictionary with the policy, content, and generated routine.

2. Reading Articles: Loads help center articles from a CSV file into the articles list.

3. Concurrent Processing: Uses ThreadPoolExecutor to process multiple articles at once, speeding up the routine generation.

##**Storing and Displaying Results**


```python
# Convert results to a DataFrame
df = pd.DataFrame(results)

# Set display options to show all text in the dataframe cells
pd.set_option('display.max_colwidth', None)

# Function to display formatted text in HTML
def display_formatted_dataframe(df):
    def format_text(text):
        return text.replace('\n', '<br>') if text else "No routine generated"

    df_formatted = df.copy()
    df_formatted['content'] = df_formatted['content'].apply(format_text)
    df_formatted['routine'] = df_formatted['routine'].apply(format_text)

    display(HTML(df_formatted.to_html(escape=False, justify='left')))

# Display the dataframe with formatted text
display_formatted_dataframe(df)
```


<table border="1" class="dataframe">
  <thead>
    <tr style="text-align: left;">
      <th></th>
      <th>policy</th>
      <th>content</th>
      <th>routine</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th>0</th>
      <td>Delete Payment Method</td>
      <td>How do I delete my payment method?<br>Updated over a week ago<br>We keep your payment method on file to cover any outstanding charges on your account. To stop charges to your payment method, please follow the steps below.<br><br>## ChatGPT <br>You can cancel your ChatGPT Plus subscription to stop further charges at any time:  <br>Click on 'My Plan' in the ChatGPT sidebar. <br>Click on 'Manage my subscription' in the pop-up window.<br>Select 'Cancel Plan'. <br>Please note that your cancellation will take effect the day after the next billing date, and you can continue using our services until then. To avoid being charged for your next billing period, please cancel your subscription at least 24 hours before your next billing date. <br><br>## API<br>We'll need to keep a payment method on file to account for any outstanding usage costs. You're welcome to cancel your pay-as-you-go service, by clicking 'Cancel paid account' in your billing overview. After the current month's invoice has been issued, the current card will no longer be charged. <br>If you'd like to continue using the service, add a new payment method in the billing overview page and select 'Set as default'. You'll then be able to delete the old payment method.<br></td>
      <td>Please note that deleting a payment method will not automatically cancel your subscription. If you'd like to cancel your service, please follow the instructions above for ChatGPT Plus or API.<br><br>## External Services<br>If you have a payment method stored with one of our external services (e.g. a third-party app), you'll need to contact the service provider directly to cancel or delete the payment method.<br><br>Please let us know if you have any other questions or if there's anything else we can help with.<br><br>I'll be happy to assist you further.</td>
    </tr>
    <tr>
      <th>1</th>
      <td>Business Associate Agreement</td>
      <td>How can I get a Business Associate Agreement (BAA) with OpenAI?<br>Information about HIPAA compliance for healthcare companies<br><br>The Health Insurance Portability and Accountability Act (HIPAA) is a U.S. federal law that requires privacy and security protections for protected health information (PHI). Our API platform can be a great fit for any covered entity or business associate looking to process protected health information, and we’d be happy to assist you in fulfilling your HIPAA compliance. To use our API platform, you’ll first need a BAA with OpenAI.<br><br><br>How do I get started?<br>If you require a BAA before you can use our API, email us at baa@openai.com with details about your company and use case.<br><br>Our team will respond within 1-2 business days. We review each BAA request on a case-by-case basis and may need additional information. The process is usually completed within a few business days.<br><br><br>Can I get a BAA for ChatGPT?<br>If you're interested in exploring a BAA for ChatGPT Enterprise, please contact sales.<br> <br><br>What happens if I’m not approved?<br>We are able to approve most customers that request BAAs, but occasionally a use case doesn’t pass our team's evaluation. In that case, we’ll give feedback and context as to why that is and give you the opportunity to update your intended use of our API and re-apply.<br><br> <br>Are all API services covered by the BAA?<br>No, only endpoints that are eligible for zero retention are covered by the BAA. You can see a list of those endpoints. <br><br> <br>Is an enterprise agreement requirement to sign a BAA?<br>No, an enterprise agreement is not required to sign a BAA.<br></td>
      <td><br>Do you provide a data processing agreement?<br>Yes, we can provide a DPA, but it is typically more expensive and requires more back-and-forth with our team.<br><br> <br>Can I obtain a copy of the executed BAA?<br>Once you sign a BAA with OpenAI, you will receive a copy of the executed agreement.<br><br><br>Please note that we are not able to provide a HIPAA-compliant API platform to companies that don’t have an enterprise agreement with us.<br><br><br>Here is the formatted routine for an internal-facing LLM:<br><br>**Main Actions:**<br><br>1. **Initial Request**<br>   a. Receive the request for a Business Associate Agreement (BAA) from the customer.<br>   b. If the request is for a BAA for ChatGPT Enterprise, then redirect the customer to contact sales.<br>   c. Otherwise, proceed to Step 1d.<br>   d. Respond to the customer with the following message: "Thank you for your interest in obtaining a Business Associate Agreement (BAA) with OpenAI. We will review your request and respond within 1-2 business days. Please provide more information about your company and use case so we can better assist you."<br>   e. `call the get_customer_info function` to retrieve the customer's company and use case details.<br><br>2. **Review and Evaluation**<br>   a. Receive the customer's company and use case details.<br>   b. Review the customer's request on a case-by-case basis.<br>   c. If additional information is required, then send a request to the customer for more details.<br>   d. `call the evaluate_request function` to determine if the customer's use case is eligible for a BAA.<br>   e. If the request is approved, then proceed to Step 3.<br>   f. If the request is not approved, then proceed to Step 4.<br><br>3. **BAA Approval and Execution**<br>   a. If the customer's request is approved, then send a confirmation message to the</td>
    </tr>
    <tr>
      <th>2</th>
      <td>Set up prepaid billing</td>
      <td>How can I set up prepaid billing?<br><br>How it works<br>Prepaid billing allows API users to pre-purchase usage. The credits you've bought will be applied to your monthly invoice. This means that any API usage you incur will first be deducted from the prepaid credits. If your usage exceeds the credits you've purchased, you'll then be billed for the additional amount.<br>Prepaid billing helps developers know what they are committing to upfront which can provide more predictability for budgeting and spend management. <br><br><br>Setting up prepaid billing<br>If you're on a Monthly Billing plan, you may also choose to switch to prepaid billing and purchase credits upfront for API usage. <br>- Go to your billing overview in your account settings<br>- Click "Start payment plan" (you may see variations like "Buy credits")<br>    Note: If you previously had an arrears billing plan, you'll need to cancel this existing payment plan first.<br>- Choose the initial amount of credits you want to purchase. The minimum purchase is $5. The maximum purchase will be based on your trust tier.<br>- Confirm and purchase your initial amount of credits.<br>- Use auto-recharge to set an automatic recharge amount, which is the amount of credits that will be added to your account when your balance falls below a set threshold.<br><br>Please note that any purchased credits will expire after 1 year and they are non-refundable. <br>After you’ve purchased credits, you should be able to start using the API. Note that there may be a couple minutes of delay while our systems update to reflect your credit balance.<br><br><br>Purchasing additional credits<br>Once you’ve consumed all your credits, your API requests will start returning an error letting you know you’ve hit your billing quota. If you’d like to continue your API usage, you can return to the billing portal and use the “Add to balance” button to purchase additional credits.<br><br> <br>Delayed billing<br>Due to the complexity of our billing and processing systems, there may be delays in our ability to cut off access after you consume all of your credits. This excess usage may appear as a negative credit balance in your billing dashboard, and will be deducted from your next credit purchase.<br></td>
      <td>Please note that there are risks of data loss and service disruption associated with exceeding your prepaid billing quota.<br><br>API Usage <br>API usage will be blocked if you do not have enough credits to cover the request. If you have remaining credits, the system will apply the usage to the credits. When the credits are depleted, the system will stop the API usage until the credits are replenished.</td>
    </tr>
    <tr>
      <th>3</th>
      <td>VAT Exemption request</td>
      <td>How do I submit a VAT exemption request?<br>Updated over a week ago<br>If you are eligible for a tax exemption and would like to apply it to your account, please follow these steps: <br><br>Depending on the state and if required: <br>1. Obtain a current tax exemption certificate from your state or local government and/or your fully completed non-profit sales tax exemption forms. The certificate/forms should include: <br>    Your name and address<br>    Tax exemption number<br>    Signatures and dates, etc. <br>2. Send us a copy of your certificate using the chat widget in the bottom-right corner. Please include your organization id, invoice number or email address associated with the account, so we can easily find you. Instructions on how to find these items are below.<br><br>3. Once we receive your certificate/form, we will review it and apply the tax exemption to your account. You will receive a confirmation email once the exemption has been applied. <br><br>Please note that the tax exemption will only apply to future purchases. We cannot apply VAT exemptions retroactively.<br><br> <br><br>Where to find your account data<br>In order to submit a Value Added Tax ('VAT') exemption request you will need the following from your organization Billing preferences:<br>    1. Company name<br>    2. Billing email<br>    3. Primary business address<br>    4. Business tax ID</td>
      <td><br>    5. Organization ID<br>    6. Invoice number<br><br>To find the organization ID and invoice number: <br>    1. Go to your Billing preferences<br>    2. Select the billing address associated with the account you are trying to apply a VAT exemption to<br>    3. Look for the organization ID and invoice number in the "Details" section<br><br> <br><br>**How to submit your VAT exemption request** <br>    1. Open the chat widget in the bottom-right corner<br>    2. Select the 'VAT exemption request' topic<br>    3. Fill in the form with the required information and attach a copy of your certificate/form<br>    4. Click 'Submit request'<br><br>You can also email your request to us. Please include the same information as the form, and attach a copy of your certificate/form.<br><br> <br><br>**What to expect after submitting your request** <br>    1. We will review your request and apply the VAT exemption to your account if everything is in order<br>    2. We will send you a confirmation email once the exemption has been applied<br>    3. If we need more information, we will contact you via email or phone<br><br> <br><br>**Additional tips** <br>    If you are unsure about the process, please contact us. We are here to help.<br>    You can also find more information on our website, including our VAT exemption policy and FAQs.<br><br>Here is the formatted routine:<br><br><br>1. Review the customer's VAT exemption request form to ensure all required information is provided.<br>    a. Check for the customer's name and address.<br>    b. Check for the tax exemption number.<br>    c. Check for signatures and dates.<br>    d. If any required information is missing, then ask the customer to provide the missing details.<br>    e. If the customer has submitted a VAT exemption request via email, then request the customer to submit the request through the chat widget.<br><br>2. Verify the customer's account information to ensure</td>
    </tr>
  </tbody>
</table>


1. **Creating a DataFrame**: Converts the results into a Pandas DataFrame for easier manipulation and viewing.
2. **Displaying the Data:** Defines a function display_formatted_dataframe(df) to format and display the DataFrame in a readable HTML format.

#**Summary**

This code converts help center articles into internal-facing routines using the Nugen API. It processes multiple articles concurrently to improve efficiency and displays the results in an easily readable format. By following the steps outlined in this documentation, you can adapt and use the code for your own routine generation needs.

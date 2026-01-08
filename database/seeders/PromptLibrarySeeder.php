<?php

namespace Database\Seeders;

use App\Models\ApprovedPrompt;
use App\Models\PromptCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromptLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $clientUpdates = PromptCategory::create([
            'name' => 'Client Updates',
            'description' => 'Templates for communicating updates to clients',
            'icon' => '📧',
            'sort_order' => 1,
        ]);

        $feeExplanations = PromptCategory::create([
            'name' => 'Fee Explanations',
            'description' => 'Templates for explaining fees and charges',
            'icon' => '💰',
            'sort_order' => 2,
        ]);

        $policySummaries = PromptCategory::create([
            'name' => 'Policy Summaries',
            'description' => 'Templates for policy information and summaries',
            'icon' => '📄',
            'sort_order' => 3,
        ]);

        $riskNotes = PromptCategory::create([
            'name' => 'Risk Notes',
            'description' => 'Templates for risk assessments and recommendations',
            'icon' => '⚠️',
            'sort_order' => 4,
        ]);

        $generalResponses = PromptCategory::create([
            'name' => 'General Responses',
            'description' => 'General purpose response templates',
            'icon' => '💬',
            'sort_order' => 5,
        ]);

        // Create prompts for Client Updates
        ApprovedPrompt::create([
            'category_id' => $clientUpdates->id,
            'title' => 'Policy Renewal Reminder',
            'content' => 'Dear [Client Name],

I hope this message finds you well. I wanted to reach out regarding your upcoming policy renewal for [Policy Type] scheduled for [Renewal Date].

To ensure uninterrupted coverage, I\'ll need to review your current information and discuss any changes to your needs. Could we schedule a brief call this week to go over your renewal?

Please let me know your availability, and I\'ll send over the renewal documents for your review.

Best regards,
[Your Name]',
            'description' => 'Template for reminding clients about upcoming policy renewals',
            'tags' => ['renewal', 'reminder', 'scheduling'],
            'is_active' => true,
        ]);

        ApprovedPrompt::create([
            'category_id' => $clientUpdates->id,
            'title' => 'Claim Status Update',
            'content' => 'Hi [Client Name],

I wanted to provide you with an update on your recent claim (#[Claim Number]) submitted on [Date].

Current Status: [Status]
Next Steps: [Next Steps]
Estimated Timeline: [Timeline]

[Additional details about the claim process or any required actions from the client]

If you have any questions or concerns, please don\'t hesitate to reach out. I\'m here to help guide you through this process.

Best regards,
[Your Name]',
            'description' => 'Template for updating clients on their claim status',
            'tags' => ['claim', 'update', 'status'],
            'is_active' => true,
        ]);

        ApprovedPrompt::create([
            'category_id' => $clientUpdates->id,
            'title' => 'Welcome New Client',
            'content' => 'Dear [Client Name],

Welcome to [Company Name]! I\'m thrilled to have you as a new client and look forward to serving your insurance needs.

I\'ve attached your policy documents and welcome packet, which includes:
- Your policy summary and coverage details
- Important contact information
- Claims reporting procedures
- Frequently asked questions

Please review these materials and let me know if you have any questions. I\'m here to ensure you understand your coverage and feel confident in your insurance decisions.

I\'ll be in touch within the next week to check in and address any questions you might have.

Welcome aboard!

Best regards,
[Your Name]',
            'description' => 'Welcome message template for new clients',
            'tags' => ['welcome', 'new client', 'onboarding'],
            'is_active' => true,
        ]);

        // Create prompts for Fee Explanations
        ApprovedPrompt::create([
            'category_id' => $feeExplanations->id,
            'title' => 'Broker Fee Explanation',
            'content' => 'Dear [Client Name],

I wanted to take a moment to explain the broker fee structure for your insurance policy.

As your insurance broker, I work on your behalf to:
- Shop multiple carriers to find the best coverage and rates
- Advocate for you during the claims process
- Provide ongoing policy management and reviews
- Offer expert advice on coverage decisions

The broker fee of [Fee Amount] covers these comprehensive services throughout your policy term. This fee is [separate from/included in] your premium and ensures you receive personalized attention and professional advocacy.

If you have any questions about this fee structure or the services provided, I\'m happy to discuss them with you.

Best regards,
[Your Name]',
            'description' => 'Template explaining broker fees to clients',
            'tags' => ['fees', 'broker', 'explanation'],
            'is_active' => true,
        ]);

        ApprovedPrompt::create([
            'category_id' => $feeExplanations->id,
            'title' => 'Premium Breakdown',
            'content' => 'Hello [Client Name],

Here\'s a detailed breakdown of your insurance premium for [Policy Type]:

Base Premium: $[Amount]
Coverage Enhancements: $[Amount]
State Fees/Taxes: $[Amount]
Broker Fee: $[Amount]
Total Premium: $[Total Amount]

Payment Schedule: [Monthly/Quarterly/Annual]
Next Payment Due: [Date]

This premium reflects your current coverage limits and deductibles. If you\'d like to explore options for adjusting your premium, we can discuss modifications to your coverage or deductibles.

Please let me know if you need clarification on any part of this breakdown.

Best regards,
[Your Name]',
            'description' => 'Template for breaking down premium costs',
            'tags' => ['premium', 'breakdown', 'costs'],
            'is_active' => true,
        ]);

        // Create prompts for Policy Summaries
        ApprovedPrompt::create([
            'category_id' => $policySummaries->id,
            'title' => 'Coverage Summary Request',
            'content' => 'Hi [Client Name],

You requested a summary of your current coverage. Here are the key details for your [Policy Type]:

Coverage Limits:
- [Coverage Type]: $[Limit]
- [Coverage Type]: $[Limit]
- [Coverage Type]: $[Limit]

Deductibles:
- [Deductible Type]: $[Amount]

Key Features:
- [Feature 1]
- [Feature 2]
- [Feature 3]

This coverage is designed to [brief explanation of how it meets their needs].

If you\'d like to review any aspects of this coverage or discuss potential adjustments, please let me know.

Best regards,
[Your Name]',
            'description' => 'Template for providing policy coverage summaries',
            'tags' => ['coverage', 'summary', 'policy'],
            'is_active' => true,
        ]);

        ApprovedPrompt::create([
            'category_id' => $policySummaries->id,
            'title' => 'Policy Terms Explanation',
            'content' => 'Dear [Client Name],

I wanted to clarify some important terms in your insurance policy to ensure you have a clear understanding of your coverage:

[Term 1]: [Clear explanation in simple language]

[Term 2]: [Clear explanation in simple language]

[Term 3]: [Clear explanation in simple language]

Understanding these terms will help you make informed decisions about your coverage and know what to expect in various situations.

If you have questions about any other policy terms or language, please don\'t hesitate to ask. I\'m here to make sure you\'re completely comfortable with your coverage.

Best regards,
[Your Name]',
            'description' => 'Template for explaining complex policy terms',
            'tags' => ['terms', 'explanation', 'clarification'],
            'is_active' => true,
        ]);

        // Create prompts for Risk Notes
        ApprovedPrompt::create([
            'category_id' => $riskNotes->id,
            'title' => 'Risk Assessment Introduction',
            'content' => 'Hello [Client Name],

As part of our comprehensive insurance review, I\'ve conducted a risk assessment of your [business/property/situation]. This assessment helps identify potential exposures and ensures your coverage adequately protects you.

Key Risk Areas Identified:
1. [Risk Area 1]: [Brief description]
2. [Risk Area 2]: [Brief description]
3. [Risk Area 3]: [Brief description]

Current Coverage Analysis:
- [Analysis of how current coverage addresses these risks]

I\'ll be following up with specific recommendations to address any gaps or areas where enhanced protection might be beneficial.

Please let me know if you\'d like to discuss any of these risk factors in more detail.

Best regards,
[Your Name]',
            'description' => 'Template for introducing risk assessment results',
            'tags' => ['risk', 'assessment', 'analysis'],
            'is_active' => true,
        ]);

        ApprovedPrompt::create([
            'category_id' => $riskNotes->id,
            'title' => 'Additional Coverage Recommendation',
            'content' => 'Dear [Client Name],

Based on my review of your current coverage and risk profile, I\'d like to recommend considering additional protection in the following area:

Recommended Coverage: [Coverage Type]
Why I\'m Recommending This: [Specific risk or exposure this addresses]
Coverage Benefits: [Key benefits and protections]
Estimated Cost: [Cost range or impact on premium]

This recommendation is based on [specific circumstances or industry trends] and would provide enhanced protection for [specific scenarios].

I\'d be happy to discuss this recommendation in detail and answer any questions you might have. We can schedule a brief call at your convenience to review the options.

Best regards,
[Your Name]',
            'description' => 'Template for recommending additional coverage',
            'tags' => ['recommendation', 'coverage', 'protection'],
            'is_active' => true,
        ]);

        // Create prompts for General Responses
        ApprovedPrompt::create([
            'category_id' => $generalResponses->id,
            'title' => 'Thank You for Your Business',
            'content' => 'Dear [Client Name],

I wanted to take a moment to thank you for choosing [Company Name] for your insurance needs. Your trust in our services means a great deal to me.

I\'m committed to providing you with exceptional service and ensuring you have the right coverage to protect what matters most to you. If you ever have questions, concerns, or need assistance with your policy, please don\'t hesitate to reach out.

I also appreciate any referrals you might send our way. There\'s no greater compliment than a client who feels confident recommending our services to their friends and family.

Thank you again for your business, and I look forward to serving you for years to come.

Best regards,
[Your Name]',
            'description' => 'General thank you message for client appreciation',
            'tags' => ['thank you', 'appreciation', 'relationship'],
            'is_active' => true,
        ]);
    }
}

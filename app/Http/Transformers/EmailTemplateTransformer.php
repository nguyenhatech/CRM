<?php

namespace Nh\Http\Transformers;

use Nh\Repositories\EmailTemplates\EmailTemplate;
use League\Fractal\TransformerAbstract;

class EmailTemplateTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'client'
    ];

     public function transform(EmailTemplate $emailTemplate = null)
    {
        if (is_null($emailTemplate)) {
            return [];
        }

        $data = [
            'id'         => $emailTemplate->uuid,
            'name'       => $emailTemplate->name,
            'client_id'  => $emailTemplate->client_id,
            'template'   => $emailTemplate->template,
            'created_at' => $emailTemplate->created_at ? $emailTemplate->created_at->format('d-m-Y H:i:s') : null,
            'updated_at' => $emailTemplate->updated_at ? $emailTemplate->updated_at->format('d-m-Y H:i:s') : null
        ];

        return $data;
    }

    public function includeClient(EmailTemplate $emailTemplate = null)
    {
        if (is_null($emailTemplate)) {
            return $this->null();
        }

        return $this->item($emailTemplate->client, new UserTransformer);
    }
}

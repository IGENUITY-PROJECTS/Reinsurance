<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    private function authorize(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'super-admin']), 403);
    }

    public function index(Request $request)
    {
        $this->authorize($request);
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'category' => 'nullable|string|max:20']);
        $query = Company::query();
        if (filled($filters['q'] ?? null)) {
            $term = '%'.trim($filters['q']).'%';
            $query->where(fn ($q) => $q->where('CmpCode', 'like', $term)->orWhere('CompanyName', 'like', $term)
                ->orWhere('ShortName', 'like', $term)->orWhere('EmailAdd', 'like', $term)
                ->orWhere('CountryName', 'like', $term)->orWhere('MTown', 'like', $term));
        }
        if (filled($filters['category'] ?? null)) {
            $query->where('CmpSubTypeCode', $filters['category']);
        }

        return view('admin.companies.index', [
            'companies' => $query->orderBy('CompanyName')->orderBy('CmpCode')->paginate(15)->withQueryString(),
            'categories' => Company::query()->select('CmpSubTypeCode')->selectRaw('MAX(CmpSubTypeDesc) as description')
                ->whereNotNull('CmpSubTypeCode')->groupBy('CmpSubTypeCode')->orderBy('CmpSubTypeCode')->get(),
        ]);
    }

    public function show(Request $request, Company $company)
    {
        $this->authorize($request);

        return view('admin.companies.show', compact('company'));
    }
}

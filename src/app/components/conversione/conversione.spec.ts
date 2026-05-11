import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Conversione } from './conversione';

describe('Conversione', () => {
  let component: Conversione;
  let fixture: ComponentFixture<Conversione>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Conversione],
    }).compileComponents();

    fixture = TestBed.createComponent(Conversione);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
